# Symfony Template

Ce projet est un squelette (template) pour les nouvelles applications Symfony, pré-configuré avec les outils modernes de
développement.

## Fonctionnalités

- **Framework** : Symfony 8.0+
- **Serveur local** : Intégration complète avec le CLI Symfony (proxy HTTPS `*.wip`)
- **Base de données** : PostgreSQL 18 via Docker Compose
- **Assets** : Tailwind CSS 4 via Symfony UX
- **E-mails** : Mailpit pour la capture des mails en développement
- **Auth** : Authentification par formulaire (email/password)
- **Tests** : PHPUnit 12 (Unit + Functional) + Playwright (E2E)
- **Qualité** : PHPStan (level 9) + PHP-CS-Fixer
- **Async** : Symfony Messenger (transport Doctrine)
- **AI** : Serveurs MCP intégrés (Symfony AI Mate, Playwright, Chrome DevTools)

## Prérequis

- [PHP 8.4+](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [Docker](https://www.docker.com/) & [Docker Compose](https://docs.docker.com/compose/)
- [Symfony CLI](https://symfony.com/download)
- [Node.js](https://nodejs.org/) (pour Playwright et les outils MCP)

## Installation

1. **Cloner le projet**
2. **Installer les dépendances**
   ```bash
   symfony composer install
   npm install
   ```
3. **Configurer les variables d'environnement**

   Copiez le fichier `.env` en `.env.local` et adaptez les variables.
4. **Créer la base de données**
   ```bash
   symfony console doctrine:database:create --if-not-exists
   symfony console doctrine:migrations:migrate -n
   symfony console doctrine:fixtures:load -n
   ```
5. **Démarrer le serveur Symfony**
   ```bash
   symfony serve
   ```

## Workflow de développement

### Commandes utiles

- **Lancer les workers (Tailwind, etc.)** :
  Le fichier `.symfony.local.yaml` est configuré pour lancer automatiquement Tailwind en mode watch via le CLI Symfony
  ainsi que la stack Docker.
- **Accéder à la base de données** :
  L'utilisateur et le nom de la base sont définis par la variable par défaut `template`.
- **Mailpit** :
  L'interface web est accessible sur `http://localhost:8027`.

### Tests

```bash
symfony php bin/phpunit                              # Tests Unit + Functional
npm run test:e2e                                     # Tests E2E Playwright
npx playwright test tests/e2e/login.spec.ts          # Un test E2E spécifique
```

### Qualité de code

```bash
symfony php vendor/bin/phpstan analyse               # Analyse statique (level 9)
symfony php vendor/bin/php-cs-fixer fix              # Code style
```

## Serveurs MCP (Claude Code)

Le fichier `.mcp.json` configure trois serveurs MCP pour l'assistance IA :

| Serveur             | Description                                                    |
|---------------------|----------------------------------------------------------------|
| **symfony-ai-mate** | Accès au profiler Symfony, logs Monolog, services du container |
| **playwright**      | Automatisation navigateur pour tests et debug                  |
| **chrome-devtools** | Interaction avec Chrome via DevTools Protocol                  |

## État d'avancement (v1.0.0)

- [x] Configuration Symfony CLI
- [x] Configuration Docker local (PostgreSQL, Mailpit)
- [x] Installation Tailwind CSS 4
- [x] Configuration PHP-CS-Fixer / PHPStan
- [x] Configuration Editorconfig
- [x] Création du template de base (base.html.twig)
- [x] Installation Symfony UX (Icons, Flash Messages, Live Component)
- [x] Configuration Mailpit & Tests Email
- [x] Authentification par formulaire (login/logout)
- [x] Tests E2E avec Playwright (migration depuis Panther)
- [x] Serveurs MCP (Symfony AI Mate, Playwright, Chrome DevTools)
