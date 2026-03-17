# CLAUDE.md

## Vue d'ensemble

**Symfony Template** — Template de demarrage Symfony 8 avec authentification, Tailwind CSS et outillage complet.

## Stack

- **Backend** : Symfony 8.0, PHP 8.4+ (`declare(strict_types=1)` partout)
- **DB** : PostgreSQL 18
- **Frontend** : Tailwind CSS 4, Stimulus, Symfony UX (Live Components, Turbo, Icons)
- **Auth** : Form login (email/password)
- **Tests** : PHPUnit 12 (Unit + Functional) + Playwright (E2E)
- **Dev** : Symfony CLI (proxy HTTPS sur `*.wip`) + Docker Compose (services externes uniquement)
- **Async** : Symfony Messenger (transport Doctrine)

## Commandes

```bash
symfony serve                                        # Serveur dev
symfony console doctrine:database:drop --force --if-exists && \
  symfony console doctrine:database:create --if-not-exists && \
  symfony console doctrine:migrations:migrate -n && \
  symfony console doctrine:fixtures:load -n          # Reset DB complet

symfony console make:migration                       # Apres modif entite
symfony console doctrine:migrations:migrate -n       # Appliquer migrations
symfony console tailwind:build --watch               # Tailwind

symfony php bin/phpunit                              # Tests Unit + Functional
npm run test:e2e                                     # Tests E2E Playwright
npx playwright test tests/e2e/login.spec.ts          # Un test E2E specifique

symfony php vendor/bin/phpstan analyse               # Analyse statique (level 9)
symfony php vendor/bin/php-cs-fixer fix              # Code style
```

## Structure

```
src/
├── Controller/
│   ├── PageController.php        # Home page + routes utilitaires
│   └── SecurityController.php    # Login, logout
├── Entity/
│   └── User.php                  # Entite User (email, roles, password)
├── Repository/
│   └── UserRepository.php        # PasswordUpgraderInterface
└── Kernel.php

fixtures/                          # Fixtures Doctrine (PSR-4: DataFixtures\) — PAS dans src/DataFixtures/
tests/
├── bootstrap.php
└── e2e/
    └── login.spec.ts              # E2E login flow (Playwright)

templates/
├── base.html.twig                 # Layout principal
├── security.html.twig             # Layout securite
├── common/
│   └── flash-messages.html.twig
├── page/
│   └── index.html.twig
└── security/
    └── login.html.twig

assets/
├── app.js                         # Entrypoint JS
├── stimulus_bootstrap.js          # Init Stimulus
├── controllers/                   # Stimulus controllers
└── styles/                        # Tailwind/CSS
```

## Securite

```yaml
- { path: ^/login, roles: PUBLIC_ACCESS }
- { path: ^/, roles: ROLE_USER }
```

## Environnement dev

**Toutes les commandes PHP passent par `symfony` CLI** (jamais `php` directement) — cela injecte automatiquement les variables Docker Compose (ports DB, SMTP, etc.).

- **Proxy** : domaine local `template.wip` (`.symfony.local.yaml`)
- **Workers** : Docker Compose, Messenger consumer (`async`), Tailwind build (`--watch`)
- **Docker Compose** (services externes uniquement, pas d'app PHP) :
    - **database** : PostgreSQL 18.1 (`template/template`, port `5434`)
    - **mailpit** : SMTP dev (port `1027`) + UI web (port `8027`)

## Architecture & Responsabilites

### Couches et flux de donnees

Request → Controller → Service/Manager → Repository → Entity → Response

### Controller (`src/Controller/`)

- Responsabilite : recevoir la requete, deleguer, retourner une reponse
- JAMAIS de logique metier, de requetes Doctrine, de calculs
- 1 action = 1 methode publique, injection des dependances par constructeur
- Retourne : Response, JsonResponse, ou RedirectResponse

### Service / Manager (`src/Service/`)

- Responsabilite : logique metier reutilisable
- Manager quand il orchestre plusieurs operations/services
- Service quand il encapsule une logique unitaire
- Peut appeler des repositories, jamais l'inverse

### Repository (`src/Repository/`)

- Responsabilite : acces donnees uniquement
- Jamais de logique metier
- QueryBuilder pour requetes complexes, methodes find* pour le simple

### Entity (`src/Entity/`)

- Responsabilite : structure de donnees + regles de domaine simples
- Getters/setters, validations de contraintes, methodes de domaine simples
- JAMAIS d'injection de service, JAMAIS d'appel repository

### Arbre de decision — "Ou placer mon code ?"

| Je dois...                      | Type de classe        | Repertoire             |
|---------------------------------|-----------------------|------------------------|
| Gerer une requete HTTP          | Controller            | `src/Controller/`      |
| Executer de la logique metier   | Service / Manager     | `src/Service/`         |
| Requeter la base de donnees     | Repository            | `src/Repository/`      |
| Reagir a un evenement Symfony   | EventSubscriber       | `src/EventSubscriber/` |
| Reagir a un evenement Doctrine  | EntityListener        | `src/EntityListener/`  |
| Traiter un message async        | Message + Handler     | `src/Message/`         |
| Creer un objet complexe         | Factory               | `src/Factory/`         |
| Transformer/mapper des donnees  | Mapper / Transformer  | `src/Mapper/`          |
| Definir un formulaire           | FormType              | `src/Form/`            |
| Valider une contrainte custom   | Constraint+Validator  | `src/Validator/`       |
| Envoyer un email                | Classe Mailer dediee  | `src/Mailer/`          |
| Ajouter un filtre/fonction Twig | TwigExtension+Runtime | `src/Twig/`            |
| Composant interactif serveur    | Live Component        | `src/Twig/Components/` |
| Enum metier                     | Backed string enum    | `src/Enum/Type/`       |

### Anti-patterns (a ne JAMAIS faire)

- Controller qui contient du QueryBuilder ou de la logique metier
- Un QueryBuilder en dehors d'un repository
- Entity qui injecte un service ou appelle un repository
- Service qui retourne une Response HTTP
- Repository qui contient de la logique metier
- Logique dans un template Twig (au-dela d'affichage conditionnel simple)
- `new Service()` au lieu de l'injection de dependances

## Conventions

### Nommage

- **Classes** : PascalCase, suffixees par leur type (`UserRepository`, `InvoiceManager`, `OrderCreatedEvent`)
- **Methodes** : camelCase, verbe d'action (`createUser`, `findByEmail`, `handleOrderCreated`)
- **Templates** : snake_case, miroir de la route (`security/login.html.twig`)
- **Routes** : snake_case prefixees par domaine (`app_login`, `app_page`)

### Doctrine

- **Entites** : `inversedBy`/`mappedBy` bidirectionnels, `ArrayCollection` dans constructeurs, `__toString()` pour l'admin
- **Contraintes d'unicite** : pattern `UNIQ_{TABLE}_{CHAMP}`
- **Migrations** : ne jamais modifier une migration commitee, en creer une nouvelle

### Services

- Injection par constructeur uniquement (pas de setter injection)
- 1 service = 1 responsabilite

### Enums & Mailer

- **Enums** : backed string enums dans `src/Enum/Type/`
- **Mailer** : classes dediees dans `src/Mailer/` avec `TemplatedEmail`

## Tests

### PHPUnit (Unit + Functional)

- **Unit** : `createStub()` sans attentes, `createMock()` avec `expects()` (PHPUnit 12 notices sinon)
- **Functional** : `WebTestCase`/`KernelTestCase` — vraie DB

### Playwright (E2E)

- Tests dans `tests/e2e/` (TypeScript)
- Config : `playwright.config.ts`, baseURL `https://template.wip`, sequentiel (`workers: 1`)
- Selecteurs : privilegier `data-test="..."` plutot que classes CSS

### Identifiants de test

- `admin@example.com` / `password` (ROLE_USER)

## Workflow — Cycle de developpement par ticket

Chaque ticket suit ce cycle en 4 phases. Ne jamais passer a la phase suivante sans validation explicite du user.

```
PLAN → ANALYZE → BUILD → TEST
 ↑                         │
 └─── retour si regression ┘
```

### Phase 1 — PLAN

Comprendre le ticket avant d'ecrire du code. Entrer en `EnterPlanMode`.

- Reformuler : "Cette US permet a [qui] de [quoi] afin de [pourquoi]."
- Identifier : entites Doctrine, patterns Symfony (EventSubscriber, Live Component, Workflow, Messenger), risques, dependances
- Decouper en sous-taches atomiques

**Checkpoint** : presenter reformulation + entites + patterns + sous-taches → attendre validation.

### Phase 2 — ANALYZE

Lire le code existant avant de concevoir.

- Lire services, repositories, entites, templates concernes
- Choisir l'approche selon la philosophie projet :
    - `EventSubscriber`/`EntityListener` > surcharges directes
    - Live Components pour interactivite serveur (pas de JS custom)
    - Messenger pour traitements async
- Definir fichiers a creer/modifier et strategie de test

**Checkpoint** : presenter approche + fichiers + justification → attendre validation.

### Phase 3 — BUILD

Implementer sous-tache par sous-tache.

**Ordre** : Modele (entite/migration) → Logique metier (service) → Integration (subscriber/workflow) → Interface (component/template)

**Apres chaque sous-tache** :

```bash
symfony php vendor/bin/phpstan analyse
symfony php vendor/bin/php-cs-fixer fix
```

**Checkpoint** : presenter fichiers modifies + comportement + reste a faire → attendre validation.

### Phase 4 — TEST

| Code ecrit                       | Test requis                   |
|----------------------------------|-------------------------------|
| Service / Command Handler        | Unit (`createStub()`)         |
| Repository custom                | Functional (`KernelTestCase`) |
| EventSubscriber / EntityListener | Unit (declenchement manuel)   |
| Workflow / StateMachine          | Functional (`KernelTestCase`) |
| Template / UI / parcours         | E2E Playwright (`data-test`)  |

**Actions** : ecrire les tests, lancer PHPUnit + PHPStan + CS-Fixer, verifier 0 regressions.

**Checkpoint final** : resultats tests + regressions + pret pour review → attendre validation.

### Regles permanentes

- Toujours utiliser `symfony` CLI pour executer PHP (`symfony php`, `symfony console`) — jamais `php` directement
- Ne jamais modifier `vendor/`
- Toute modif de schema = migration generee par `symfony console make:migration` et relue
- Ne jamais modifier une migration commitee
- Ambiguite en PLAN/ANALYZE → poser la question avant de coder
- Probleme bloquant en BUILD → remonter immediatement
- Pas de `dump()`, `var_dump()`, `dd()` dans le code commite

