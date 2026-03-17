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

### Types de classes PHP dans un projet Symfony

## HTTP / Application

- Controller : classe qui gère une requête HTTP et retourne une réponse.
- Command : classe CLI exécutée via `symfony console`.
- Service : classe métier réutilisable contenant de la logique applicative. Très générique à éviter.
- Manager : service regroupant plusieurs opérations métier.
- Factory : classe responsable de la création d’objets.
- Builder : construit un objet complexe étape par étape.
- Helper : utilitaire simple pour factoriser une logique spécifique.
- Resolver : détermine dynamiquement une valeur ou un comportement.
- Provider : fournit des données depuis une source donnée.
- Processor : applique un traitement sur une donnée.
- Mapper : transforme une structure de données en une autre.
- Transformer : convertit une valeur d’un format vers un autre.
- Hydrator : remplit un objet à partir de données brutes.

## Doctrine / Data

- Entity : objet Doctrine représentant une donnée persistée en base.
- Repository : classe qui gère les requêtes et accès aux entités Doctrine.
- Embeddable : objet intégré dans une entité sans table dédiée.
- MappedSuperclass : classe de base Doctrine mutualisant des champs.
- EntityListener : réagit aux événements d’une entité Doctrine.
- DoctrineEventListener : écoute les événements globaux Doctrine.
- DoctrineEventSubscriber : version déclarative des listeners Doctrine.
- CustomType : type Doctrine personnalisé pour un format spécifique.
- Fixture : charge des données de test ou initiales.
- Migration : décrit une évolution du schéma de base.

## Sécurité

- User : entité représentant un utilisateur authentifié (implémente UserInterface).
- UserProvider : charge un utilisateur depuis une source (BDD, API, etc).
- Authenticator : gère le processus d’authentification.
- Voter : centralise la logique d’autorisation.
- UserChecker : effectue des vérifications supplémentaires sur un utilisateur.
- PasswordHasher : gère le hash et la vérification des mots de passe.
- AccessDeniedHandler : personnalise la réponse en cas d’accès refusé.
- LogoutHandler : exécute une logique lors de la déconnexion.

## Form

- FormType : définit la structure et les champs d’un formulaire.
- FormTypeExtension : étend un type de formulaire existant.
- FormEventListener : réagit aux événements du formulaire.
- FormEventSubscriber : version déclarative des listeners de formulaire.
- DataTransformer : convertit les données entre formulaire et objet.

## Validation

- Constraint : définit une règle de validation.
- ConstraintValidator : implémente la logique d’une contrainte.

## Events

- Event : objet transportant des données lors d’un événement.
- EventListener : classe qui écoute un événement précis.
- EventSubscriber : classe déclarant les événements auxquels elle réagit.

## Messenger

- Message : objet représentant une action ou donnée à traiter.
- MessageHandler : classe qui traite un message.
- Middleware : couche intermédiaire dans le traitement des messages.

## Serializer

- Normalizer : transforme un objet en données sérialisables.
- Denormalizer : reconstruit un objet à partir de données.
- Encoder : convertit les données en JSON, XML ou autre format.

## Twig

- TwigExtension : ajoute fonctions et filtres personnalisés.
- TwigRuntime : contient la logique appelée par Twig.

## Cache

- CacheWarmer : prépare des données pour améliorer les performances.
- CacheClearer : nettoie des caches spécifiques.

## Dependency Injection

- CompilerPass : modifie le container de services à la compilation.
- Extension : charge et configure les services d’un bundle.
- ServiceSubscriber : déclare explicitement les services utilisés.
- ServiceLocator : fournit dynamiquement un sous-ensemble de services.

## Intégration / technique

- Mailer : classe qui envoie des emails.
- Notifier : envoie des notifications multi-canaux.
- HttpClient : effectue des appels HTTP externes.
- ApiClient : encapsule un service externe.
- WebhookHandler : traite des appels entrants externes.
- Uploader : gère l’upload et le stockage de fichiers.
