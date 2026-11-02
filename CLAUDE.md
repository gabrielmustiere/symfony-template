# CLAUDE.md

Template Symfony 8 avec authentification, Tailwind CSS 4, PHPUnit 13, Playwright.

**Tradeoff :** ces directives privilégient la prudence sur la vitesse. Pour les tâches triviales, utilise ton jugement.

## 1. Réfléchir avant de coder

**Ne pas supposer. Ne pas cacher la confusion. Exposer les tradeoffs.**

Avant d'implémenter :

- Énoncer explicitement les hypothèses. En cas de doute, demander.
- Plusieurs interprétations possibles ? Les présenter — ne pas choisir en silence.
- Une approche plus simple existe ? Le dire. Pousser le débat quand c'est justifié.
- Quelque chose n'est pas clair ? S'arrêter. Nommer ce qui bloque. Demander.

Toute ambiguité → poser la question. Tout problème bloquant → remonter immédiatement.

## 2. Simplicité d'abord

**Le minimum de code qui résout le problème. Rien de spéculatif.**

- Aucune fonctionnalité au-delà de ce qui est demandé.
- Aucune abstraction pour un usage unique.
- Aucune "flexibilité" ou "configurabilité" non demandée.
- Aucune gestion d'erreur pour scénarios impossibles.
- 200 lignes alors que 50 suffisaient ? Réécrire.

Question à se poser : "Un dev sénior trouverait-il cela sur-conçu ?" Si oui, simplifier.

## 3. Modifications chirurgicales

**Ne toucher que ce qui doit l'être. Nettoyer uniquement ses propres miettes.**

- Ne pas "améliorer" le code adjacent, les commentaires, le formatage.
- Ne pas refactorer ce qui n'est pas cassé.
- Respecter le style existant, même si différent du tien.
- Code mort sans rapport repéré ? Le signaler — pas le supprimer.
- Imports/variables/fonctions devenus orphelins **par ta modif** → à supprimer.

Test : chaque ligne modifiée doit se rattacher directement à la demande utilisateur.

## 4. Exécution pilotée par l'objectif

**Définir le critère de succès. Itérer jusqu'à vérification.**

Transformer les tâches en objectifs vérifiables :

- "Ajouter une validation" → "Écrire les tests pour les entrées invalides, puis les faire passer"
- "Corriger le bug" → "Écrire un test qui le reproduit, puis le faire passer"
- "Refactor X" → "Tests verts avant et après"

Pour le multi-étapes : suivre le pipeline forge — 0 décor, 1 cadrage (pitch / plan), 2 implémentation, 3 clôture (review → sync → commit) (voir skill `/forge:help`). **Ne jamais passer à la phase suivante sans validation du user.**

## Stack

- PHP 8.5+ (`declare(strict_types=1)` partout), SQLite (`var/data.db`), Symfony 8.1, Symfony Messenger (Doctrine)
- Frontend : Tailwind CSS 4, Stimulus, Symfony UX (Live Components, Turbo, Icons)
- Tests : PHPUnit 13 + Playwright (E2E)
- Qualité : Mago (formatage, lint, analyse statique, `guard` pour les règles d'architecture ; config `mago.toml`) + PHP Insights (`tools/phpinsights`, style délégué à Mago)
- AI : Symfony AI Mate en CLI (`symfony php vendor/bin/mate`, voir `AGENTS.md`) configuré dans `mate/` (extensions symfony + monolog) ; extensions maison dans `Mate\` (`mate/src/`)

## Commandes

Toutes les commandes PHP passent par `symfony` CLI — jamais `php` directement.

```bash
make serve                                # Lance le serveur Symfony (Mailpit : docker compose up -d)
make db-reset                             # Reset DB complet (drop + migrate + fixtures)
symfony console make:migration            # Après modif d'une entité
make phpunit                              # PHPUnit (Unit + Functional)
make playwright                           # Playwright (E2E)
make quality                              # Mago (fmt + lint + analyze + guard)
make qa-full                              # quality + PHP Insights + PHPUnit
```

## Règles critiques

- Fixtures dans `fixtures/` (PSR-4 : `DataFixtures\`) — **PAS** dans `src/DataFixtures/`
- Ne jamais modifier une migration commitée — en créer une nouvelle
- Ne jamais modifier `vendor/`
- Pas de `dump()`, `var_dump()`, `dd()` dans le code commité
- Toute modif de schéma = migration générée par `symfony console make:migration`
- PHPUnit 13 : `createStub()` sans attentes, `createMock()` avec `expects()`
- Playwright : sélecteurs `data-test="..."`, config dans `playwright.config.ts`
- Enums : backed string enums dans `src/Enum/Type/`
- Mailer : classes dédiées dans `src/Mailer/` avec `TemplatedEmail`

## Identifiants de test

- `admin@example.com` / `password` (ROLE_USER)

## Architecture

```
Request → Controller → Service/Manager → Repository → Entity → Response
```

**Interdit** : QueryBuilder hors repository, logique métier dans controller/entity/repository, `new Service()`, entity qui injecte un service.

Vérifié par `make guard` (`mago.toml`, section `guard`) : QueryBuilder hors `App\Repository`, dépendance `App\Entity` → `App\Service`, contrôleurs `final` suffixés `Controller`, repositories suffixés `Repository`, `App\Enum\Type` réservé aux enums.

## Skills disponibles

Deux plugins structurent le travail sur ce dépôt :

### Plugin `forge` — pipeline de développement

Marketplace `gabrielmustiere/forge`. Quatre phases (0 décor, 1 cadrage, 2 implémentation, 3 clôture), trois tracks (feature, refacto, tech). Artifacts dans `docs/story/NNN-<f|r|t>-<slug>/`.

| Skill                                                                  | Usage                                               |
|------------------------------------------------------------------------|-----------------------------------------------------|
| `/forge:help`, `/forge:status`                                         | Vue d'ensemble du pipeline, point de situation      |
| `/forge:vision`, `/forge:product-backlog`, `/forge:stack`              | Phase 0 : vision, backlog, stack                    |
| `/forge:feature-interview`, `/forge:feature-pitch`, `/forge:feature-plan`, `/forge:feature-implem` | Track feature |
| `/forge:refactor-plan`, `/forge:refactor-implem`                       | Track refacto                                       |
| `/forge:tech-plan`, `/forge:tech-implem`                               | Track technique                                     |
| `/forge:review`, `/forge:sync`, `/forge:commit`                        | Clôture                                             |
| `/forge:release`                                                       | Release versionnée                                  |
| `/forge:test-scenario`                                                 | Jouer un scénario utilisateur (Playwright/DevTools) |
| `/forge:adr`, `/forge:grill`, `/forge:estimate`, `/forge:doc-feature`  | Transverses                                         |
| `/forge:agents-md`, `/forge:rules`, `/forge:backfill-metadata`         | Maintenance des instructions et metadata            |

### Plugin `symfony` — recettes framework

Marketplace `gabrielmustiere/skills`. À invoquer quand on touche au domaine concerné (controllers, doctrine, forms, events, messenger, validation, etc.).

| Domaine        | Skills                                                                                          |
|----------------|-------------------------------------------------------------------------------------------------|
| HTTP           | `/symfony:routing-define`, `/symfony:controller-action`                                         |
| Doctrine       | `/symfony:doctrine-entity`, `/symfony:doctrine-migration`, `/symfony:doctrine-query`            |
| Forms          | `/symfony:form-type`, `/symfony:form-render`, `/symfony:form-handle`, `/symfony:form-advanced`  |
| Events         | `/symfony:event-dispatch`, `/symfony:event-listen`, `/symfony:event-subscribe`                  |
| Services / DI  | `/symfony:service-define`, `/symfony:service-wire`, `/symfony:service-tags`                     |
| Validation     | `/symfony:validation-constraints`, `/symfony:validation-groups`, `/symfony:validation-use`      |
| HTTP Client    | `/symfony:http-client-request`, `/symfony:http-client-async`, `/symfony:http-client-response`, `/symfony:http-client-test` |
| Messenger      | `/symfony:messenger-async`                                                                      |
| Serializer / Mapper | `/symfony:serializer-use`, `/symfony:object-mapper`                                        |

Préfèrer ces skills aux conventions ad hoc — elles encodent les patterns retenus pour ce template.

---

**Ces directives fonctionnent si :** moins de modifications inutiles dans les diffs, moins de réécritures dues à la sur-conception, et les questions de clarification arrivent **avant** l'implémentation plutôt qu'après les erreurs.

<!-- BEGIN AI_MATE_AGENTS_IMPORT -->
@AGENTS.md
<!-- END AI_MATE_AGENTS_IMPORT -->
