init: docker-down-clear \
	docker-pull docker-build docker-up \
	console-symlink-create \
	test-start-up test-hello-world \
	wait-db migrate  validate-schema load-fixtures

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build

docker-try-build-prod: docker-down-clear-prod docker-up-prod test-start-up test-hello-world

docker-down-clear-prod:
	COMPOSE_PROJECT_NAME=bgl-prod docker-compose down -v --remove-orphans

docker-up-prod:
	COMPOSE_PROJECT_NAME=bgl-prod docker-compose -f docker-compose-prod.yml up -d

check: lint analyze test validate-schema

lint: php-lint cs-check

analyze: php-stan psalm

php-lint:
	docker-compose run --rm api-php-cli composer lint

cs-check:
	docker-compose run --rm api-php-cli composer cs-check

psalm:
	docker-compose run --rm api-php-cli composer psalm

psalm-alter:
	docker-compose run --rm api-php-cli composer psalm --alter --issues=MissingParamType --dry-run

php-stan:
	docker-compose run --rm api-php-cli composer phpstan

test:
	docker-compose run --rm api-php-cli composer test && \
	make load-fixtures

test-acceptance:
	docker-compose run --rm api-php-cli composer test -- Acceptance && \
	make load-fixtures

test-api:
	docker-compose run --rm api-php-cli composer test -- Api && \
	make load-fixtures

test-start-up:
	docker-compose run --rm api-php-cli composer test -- StartUp

test-hello-world:
	docker-compose run --rm api-php-cli composer test -- tests/Acceptance/HelloWorldCest.php

test-auth-register:
	docker-compose run --rm api-php-cli composer test -- tests/Api/V1/Auth/SignUpCest.php && \
	make load-fixtures

test-unit:
	docker-compose run --rm api-php-cli composer test -- Unit

test-coverage: test-coverage-clear
	docker-compose run --rm api-php-cli composer test -- Unit --coverage --coverage-html

test-coverage-clear:
	docker-compose run --rm api-php-cli sh -c 'rm -rf var/.tests/*'

var-all:
	docker-compose run --rm api-php-cli sh -c 'chmod 777 var'

console-symlink-create: console-symlink-clear
	docker-compose run --rm api-php-cli ln -s ./cli/app.php ./app

console-symlink-clear:
	docker-compose run --rm api-php-cli rm -f ./app

generate-api:
	docker-compose run api-php-cli ./vendor/bin/openapi ./src -o web/assets/openapi.json -f json

wait-db:
	docker-compose run api-php-cli /usr/local/bin/wait-for-it.sh db-postgres:5432

validate-schema:
	docker-compose run api-php-cli php app orm:validate-schema

migrate:
	docker-compose run api-php-cli php app migrations:migrate --no-interaction

migrate-generate:
	docker-compose run api-php-cli php app migrations:generate --no-interaction

load-fixtures:
	docker-compose run api-php-cli php app fixtures:load
