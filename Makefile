# Makefile
#
# This file contains the commands most used in DEV.
#
# The commands are to be organized semantically and alphabetically.
# The goal is that similar commands are next to each other and we can compare them and update them easily.
#
# For example in a format like `subject-action-environment`, ie:
#
#   test:               # Clear cache, run static analysis and tests.a

# Suppresses Make-specific output. Remove for more debugging info.
.SILENT:

# Make commands be run with `bash` instead of the default `sh`
#SHELL='/usr/bin/env bash'

all: help

install-dev: ## Install development settings for local development
	composer install
	php artisan ide-helper:generate
	mkdir -p env
	php artisan key:generate
	#TO DO: create database
	php artisan migrate
	#TO DO: seed with some fixture data maybe

clean: ## Clean local data
	@echo -n "This will clear your local data. Are you sure you want to do this? [y/N] " && read ans && [ $${ans:-N} = y ]
	php artisan migrate:fresh --seed

start-dev:  ## Start local development setup
	echo "Installing dependencies"
	composer install
	yarn
	yarn build
	${MAKE} serve-dev

serve-dev:  ## Start the local development server
	php artisan serve

test: ## Runs tests
	echo "Clearing cache and running tests."
	php artisan route:clear && php artisan config:clear
	php artisan security-check:now
	vendor/bin/phpstan analyse
	vendor/bin/phpcs
	yarn lint
	yarn test
	vendor/bin/psalm

test-php: ## Test PHP
	echo "Clearing cache and running tests"
	php artisan route:clear && php artisan config:clear
	php artisan security-check:now
	vendor/bin/phpstan analyse
	vendor/bin/phpcs
	vendor/bin/psalm

test-js: ## Test Javascript/CSS
	yarn lint
	yarn test

install-mpa: ## Install composer and npm dependencies
	composer install
	php artisan key:generate
	npm ci && npm run build
	php artisan migrate

add-user: # Add admin user
	php artisan user:admin 'admin@example.nl' 'Admin user' 'adminpassword1234!'

update-db-schema: ## Update database schema
	echo "Downloading database repo"
	git clone git@github.com:minvws/nl-rdo-databases ./database/repo
	echo "Building db schema"
	find ./database/repo/meldportaal_db/v* -type f -name '*.sql' | sort -V | xargs cat > ./database/schema/pgsql-schema.sql
	echo "Removing downloaded database repo"
	rm -rf ./database/repo
	echo "Done"

help: ## Display available commands
	echo "Available make commands:"
	echo
	grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
