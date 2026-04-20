.DEFAULT_GOAL := help
.PHONY: help init up down serve stop db-create db-drop db-reset migrate migration fixtures \
        test test-unit test-e2e phpstan cs-fix build quality

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*##' $(MAKEFILE_LIST) | sort | awk -F ':.*## ' '{printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

## Installation

init: up ## Installe les dépendances, crée la base et charge les fixtures
	symfony composer install
	npm install
	npx playwright install --with-deps
	symfony console tailwind:build
	$(MAKE) db-reset

## Docker & serveur

up: ## Démarre les conteneurs Docker (Mailpit)
	docker compose up -d --wait

down: ## Arrête les conteneurs Docker
	docker compose down

serve: ## Lance le serveur Symfony en arrière-plan
	symfony serve -d

stop: ## Arrête le serveur Symfony
	symfony server:stop

## Base de données

db-create: ## Crée le fichier SQLite si besoin (auto-créé au premier migrate)
	@mkdir -p var && touch var/data.db

db-drop: ## Supprime le fichier SQLite
	@rm -f var/data.db var/data_test.db

db-reset: db-drop migrate fixtures ## Recrée la base from scratch (drop + migrate + fixtures)

migrate: ## Applique les migrations Doctrine
	symfony console doctrine:migrations:migrate -n

migration: ## Génère une nouvelle migration depuis le diff d'entités
	symfony console make:migration

fixtures: ## Charge les fixtures Doctrine
	symfony console doctrine:fixtures:load -n

## Tests

test: test-unit ## Alias de test-unit

test-unit: ## Lance les tests PHPUnit (Unit + Functional)
	symfony php bin/phpunit

test-e2e: ## Lance les tests E2E Playwright
	npm run test:e2e

## Qualité

phpstan: ## Analyse statique PHPStan (niveau 9)
	symfony php vendor/bin/phpstan analyse

cs-fix: ## Correction automatique avec PHP CS Fixer
	symfony php vendor/bin/php-cs-fixer fix

build: ## Build des assets (Tailwind + AssetMapper)
	symfony console tailwind:build --minify
	symfony console asset-map:compile

quality: cs-fix phpstan build ## Lance toute la QA (CS Fixer + PHPStan + build)
