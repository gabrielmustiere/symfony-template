# Symfony Template

Ce projet est un squelette (template) pour les nouvelles applications Symfony, pré-configuré avec les outils modernes de
développement.

## Fonctionnalités

- **Framework** : Symfony 8.0+
- **Serveur local** : Intégration complète avec le CLI Symfony
- **Base de données** : PostgreSQL 18 via Docker Compose
- **Assets** : Tailwind CSS via Symfony UX
- **E-mails** : Mailpit pour la capture des mails en développement

## Prérequis

- [PHP 8.5+](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [Docker](https://www.docker.com/) & [Docker Compose](https://docs.docker.com/compose/)
- [Symfony CLI](https://symfony.com/download)

## Installation

1. **Cloner le projet**
2. **Installer les dépendances**
   ```bash
   symfony composer install
    ```
3. **Configurer les variables d'environnement**

   Copiez le fichier `.env` en `.env.local` et adaptez las variables
5. **Démarrer le serveur Symfony**
   ```bash
   symfony serve
   ```

## Workflow de développement

### Commandes utiles

- **Lancer les workers (Tailwind, etc.)** :
  Le fichier `.symfony.local.yaml` est configuré pour lancer automatiquement Tailwind en mode watch via le CLI Symfony
  ainsi que la stack docker.
- **Accéder à la base de données** :
  L'utilisateur et le nom de la base sont définis par la variable par défaut `template`.
- **Mailpit** :
  L'interface web est accessible sur `http://localhost:8025`.

## État d'avancement (Todo)

- [x] Configuration Symfony CLI
- [x] Configuration Docker local
- [x] Installation Tailwind CSS
- [x] Installation Tailwind Elements
- [x] Configuration PHP-CS-Fixer / PHPStan
- [x] Configuration Editorconfig
- [x] Configuration PHPUnit / Panther
- [x] Création du template de base (base.html.twig)
- [x] Installation Symfony UX
    - [x] Icons
    - [x] Flash Messages
    - [x] Live Component
- [ ] Configuration Basic Auth
- [ ] Ajout Castor
