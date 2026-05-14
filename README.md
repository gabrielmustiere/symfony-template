# Symfony Template

[![CI](https://github.com/gabrielmustiere/symfony-template/actions/workflows/ci.yml/badge.svg)](https://github.com/gabrielmustiere/symfony-template/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.5-blue.svg)](https://www.php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-8.0-black.svg)](https://symfony.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Squelette d'application Symfony pré-configuré avec les outils modernes de développement : Tailwind CSS 4, authentification, tests E2E Playwright, qualité de code (PHPStan level 9 + PHP-CS-Fixer), et intégration MCP pour l'assistance IA.

## Stack

- **Framework** : Symfony 8.0 / PHP 8.5+
- **Serveur local** : Symfony CLI (proxy HTTPS `*.wip`)
- **Base de données** : SQLite (fichier local, zéro infra)
- **Assets** : Tailwind CSS 4 via Symfony UX
- **E-mails** : Mailpit pour la capture en développement
- **Auth** : Authentification par formulaire (email/password)
- **Tests** : PHPUnit 12 (Unit + Functional) + Playwright (E2E)
- **Qualité** : PHPStan (level 9) + PHP-CS-Fixer
- **Async** : Symfony Messenger (transport Doctrine)
- **AI** : Serveurs MCP intégrés (Symfony AI Mate, Playwright, Chrome DevTools)

## Prérequis

- [PHP 8.5+](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)
- [Docker](https://www.docker.com/) (uniquement pour Mailpit)
- [Node.js 22+](https://nodejs.org/) (pour Playwright et Tailwind)

## Démarrer depuis ce template

1. Cliquez sur **"Use this template"** sur GitHub pour créer un nouveau dépôt.
2. Clonez votre nouveau dépôt et installez :
   ```bash
   make init
   ```
3. (Optionnel) Renommez le domaine local dans `.symfony.local.yaml` (clé `proxy.domains`) et dans `.env` (`DEFAULT_URI`) — par défaut `template.wip`.
4. (Optionnel) Régénérez `APP_SECRET` dans `.env.dev` :
   ```bash
   php -r "echo bin2hex(random_bytes(16)).PHP_EOL;"
   ```
5. Lancez l'application :
   ```bash
   make start
   ```
   L'application est accessible sur https://template.wip (ou le domaine que vous avez choisi).

## Workflow Makefile

Toutes les opérations courantes passent par `make`. Lancez `make help` pour la liste complète.

| Commande            | Description                                                |
|---------------------|------------------------------------------------------------|
| `make init`         | Installation complète (deps + DB + fixtures)               |
| `make install`      | Réinstalle les dépendances PHP/JS/Playwright               |
| `make start`        | Démarre Docker (Mailpit) + serveur Symfony                 |
| `make stop`         | Arrête le serveur Symfony                                  |
| `make db-reset`     | Recrée la base from scratch (drop + migrate + fixtures)    |
| `make migration`    | Génère une migration depuis le diff d'entités              |
| `make test`         | Lance les tests PHPUnit (Unit + Functional)                |
| `make test-e2e`     | Lance les tests E2E Playwright                             |
| `make quality`      | CS-Fixer + PHPStan + build (mode dev)                      |
| `make ci`           | Lint (dry-run) + tests unitaires (reproduit la CI)         |

## Accès aux services

- **Application** : https://template.wip
- **Mailpit (UI web)** : http://localhost:8027
- **Base SQLite (dev)** : `var/data.db` — accessible via `sqlite3 var/data.db`
- **Base SQLite (test)** : `var/data_test.db`

## Identifiants de test

- `admin@example.com` / `password` (ROLE_USER)

## Intégration continue

Le workflow `.github/workflows/ci.yml` exécute à chaque push et PR sur `main` :

1. **Lint** — PHP-CS-Fixer (dry-run) + PHPStan level 9
2. **Tests** — PHPUnit (Unit + Functional) sur SQLite
3. **E2E** — Playwright sur un serveur PHP intégré, avec Mailpit en service

Les rapports Playwright sont uploadés en artefact en cas d'échec.

## Serveurs MCP (Claude Code)

Le fichier `.mcp.json` configure trois serveurs MCP pour l'assistance IA :

| Serveur             | Description                                                    |
|---------------------|----------------------------------------------------------------|
| **symfony-ai-mate** | Accès au profiler Symfony, logs Monolog, services du container |
| **playwright**      | Automatisation navigateur pour tests et debug                  |
| **chrome-devtools** | Interaction avec Chrome via DevTools Protocol                  |

## Licence

Distribué sous licence MIT. Voir [LICENSE](LICENSE).
