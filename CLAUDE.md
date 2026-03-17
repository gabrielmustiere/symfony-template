# CLAUDE.md

## Vue d'ensemble

**Symfony Template** — Template de demarrage Symfony 8 avec authentification, Tailwind CSS et outillage complet.

## Stack

- **Backend** : Symfony 8.0, PHP 8.4+ (`declare(strict_types=1)` partout)
- **DB** : PostgreSQL 18
- **Frontend** : Tailwind CSS 4, Stimulus, Symfony UX (Live Components, Turbo, Icons)
- **Auth** : Form login (email/password)
- **Tests** : PHPUnit 12 (Unit + Functional) + Panther (E2E navigateur) + Playwright (disponible)
- **Dev** : Docker Compose (PostgreSQL, Mailpit) + Symfony CLI (proxy HTTPS sur `*.wip`)
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

symfony php bin/phpunit                              # Tests Unit + Functional + Panther
npx playwright test                                  # Tests E2E Playwright

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
└── Panther/
    └── LoginTest.php              # E2E login flow (Panther navigateur)

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

## Docker Compose

- **database** : PostgreSQL 18.1 (`template/template`, port `5434`)
- **mailpit** : SMTP dev (port `1027`) + UI web (port `8027`)

## Symfony CLI (`.symfony.local.yaml`)

- **Proxy** : domaine local `template.wip`
- **Workers** : Docker Compose, Messenger consumer (`async`), Tailwind build (`--watch`)

## Conventions

- **Entites** : `inversedBy`/`mappedBy` bidirectionnels, `ArrayCollection` dans constructeurs, `__toString()` pour l'admin
- **Contraintes d'unicite** : pattern `UNIQ_{TABLE}_{CHAMP}`
- **Migrations** : ne jamais modifier une migration commitee, en creer une nouvelle
- **Enums** : backed string enums dans `src/Enum/Type/`
- **Mailer** : classes dediees dans `src/Mailer/` avec `TemplatedEmail`

## Tests

### PHPUnit (Unit + Functional)

- **Unit** : `createStub()` sans attentes, `createMock()` avec `expects()` (PHPUnit 12 notices sinon)
- **Functional** : `WebTestCase`/`KernelTestCase` — vraie DB

### Panther (E2E navigateur)

- Tests dans `tests/Panther/`
- Utilise un vrai navigateur Chrome headless
- `$client = static::createPantherClient()`

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
| Template / UI / parcours         | E2E Panther ou Playwright     |

**Actions** : ecrire les tests, lancer PHPUnit + PHPStan + CS-Fixer, verifier 0 regressions.

**Checkpoint final** : resultats tests + regressions + pret pour review → attendre validation.

### Regles permanentes

- Ne jamais modifier `vendor/`
- Toute modif de schema = migration generee par `symfony console make:migration` et relue
- Ne jamais modifier une migration commitee
- Ambiguite en PLAN/ANALYZE → poser la question avant de coder
- Probleme bloquant en BUILD → remonter immediatement
- Pas de `dump()`, `var_dump()`, `dd()` dans le code commite
