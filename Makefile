#!/usr/bin/make

user_id := $(shell id -u)
docker_compose_bin := $(shell command -v docker-compose 2> /dev/null) --file docker/docker-compose.yml
php_bin := $(docker_compose_bin) run --rm -u $(user_id) php

.PHONY : build test fixer linter shell buildEntities
.DEFAULT_GOAL := build

# --- [ Development tasks ] -------------------------------------------------------------------------------------------

build: ## Build container and install composer libs
	$(docker_compose_bin) build
	$(php_bin) composer install

test: ## Execute library tests
	$(php_bin) vendor/bin/phpunit --configuration phpunit.xml.dist

coverage: ## Execute library tests with code coverage option
	$(php_bin) vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-html=tests/coverage

fixer: ## Run fixes for code style
	$(php_bin) vendor/bin/php-cs-fixer fix -v

linter: ## Run code checks
	$(php_bin) vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run --stop-on-violation
	$(php_bin) vendor/bin/phpcpd ./src
	$(php_bin) vendor/bin/psalm --show-info=true

shell: ## Run shell environment in container
	$(php_bin) /bin/bash

buildEntities: ## Build entities
	$(php_bin) php -f generator/generate_entities.php
	$(php_bin) vendor/bin/php-cs-fixer fix -q
