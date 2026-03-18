# CLAUDE.md

Template Symfony 8 avec authentification, Tailwind CSS 4, PHPUnit 12, Playwright.

## Stack

- PHP 8.4+ (`declare(strict_types=1)` partout), PostgreSQL 18, Symfony Messenger (Doctrine)
- Frontend : Tailwind CSS 4, Stimulus, Symfony UX (Live Components, Turbo, Icons)
- Tests : PHPUnit 12 + Playwright (E2E)

## Commandes

Toutes les commandes PHP passent par `symfony` CLI — jamais `php` directement.

```bash
symfony serve                                        # Serveur dev
symfony console doctrine:database:drop --force --if-exists && \
  symfony console doctrine:database:create --if-not-exists && \
  symfony console doctrine:migrations:migrate -n && \
  symfony console doctrine:fixtures:load -n          # Reset DB complet
symfony console make:migration                       # Apres modif entite
symfony php bin/phpunit                              # Tests Unit + Functional
npm run test:e2e                                     # Tests E2E Playwright
symfony php vendor/bin/phpstan analyse               # Analyse statique (level 9)
symfony php vendor/bin/php-cs-fixer fix              # Code style
```

## Regles critiques

- Fixtures dans `fixtures/` (PSR-4: `DataFixtures\`) — PAS dans `src/DataFixtures/`
- Ne jamais modifier une migration commitee — en creer une nouvelle
- Ne jamais modifier `vendor/`
- Pas de `dump()`, `var_dump()`, `dd()` dans le code commite
- Toute modif de schema = migration generee par `symfony console make:migration`
- PHPUnit 12 : `createStub()` sans attentes, `createMock()` avec `expects()`
- Playwright : selecteurs `data-test="..."`, config dans `playwright.config.ts`
- Enums : backed string enums dans `src/Enum/Type/`
- Mailer : classes dediees dans `src/Mailer/` avec `TemplatedEmail`

## Identifiants de test

- `admin@example.com` / `password` (ROLE_USER)

## Architecture

Request → Controller → Service/Manager → Repository → Entity → Response

**Interdit** : QueryBuilder hors repository, logique metier dans controller/entity/repository, `new Service()`, entity qui injecte un service.

Consulter `docs/architecture.md` pour l'arbre de decision et les conventions detaillees.

## Workflow

Chaque ticket suit : PLAN → ANALYZE → BUILD → TEST. Ne jamais passer a la phase suivante sans validation du user.

- PLAN : reformuler le ticket, identifier patterns/entites, decouper en sous-taches. Utiliser `EnterPlanMode`.
- BUILD : ordre Modele → Service → Integration → Template. Lancer PHPStan + CS-Fixer apres chaque sous-tache.
- TEST : voir la matrice dans `docs/workflow.md`

Ambiguite → poser la question. Probleme bloquant → remonter immediatement.
