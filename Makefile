init: docker-down-clear \
	docker-pull docker-build docker-up \
	console-symlink-create \
	test-start-up test-hello-world \
	wait-db migrate  validate-schema load-fixtures

docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans

docker-down-clear:
	docker compose down -v --remove-orphans

docker-pull:
	docker compose pull

docker-build:
	docker compose build

docker-try-build-prod: docker-down-clear-prod docker-up-prod test-hello-world-prod

docker-down-clear-prod:
	docker compose down -v --remove-orphans

docker-up-prod:
	docker compose -f docker-compose-prod.yml up -d

check: lint analyze test-clean test-build test validate-schema

lint: php-lint cs-check

analyze: php-stan psalm

php-lint:
	docker compose run --rm api-php-cli composer lint

cs-check:
	docker compose run --rm api-php-cli composer cs-check

psalm:
	docker compose run --rm api-php-cli composer psalm

psalm-alter:
	docker compose run --rm api-php-cli composer psalm --alter --issues=MissingParamType --dry-run

php-stan:
	docker compose run --rm api-php-cli composer phpstan

test-clean:
	docker compose run --rm api-php-cli ./vendor/bin/codecept clean

test-build:
	docker compose run --rm api-php-cli ./vendor/bin/codecept build

test:
	docker compose run --rm api-php-cli composer test && \
	make load-fixtures

test-acceptance:
	docker compose run --rm api-php-cli composer test -- Acceptance && \
	make load-fixtures

test-seed:
	docker compose run --rm api-php-cli composer test -- Api --seed 2014723595 && \
	make load-fixtures

test-api:
	docker compose run --rm api-php-cli composer test -- Api && \
	make load-fixtures

test-start-up:
	docker compose run --rm api-php-cli composer test -- StartUp

test-start-up-prod:
	docker compose run --rm api-php-cli ./vendor/bin/codecept clean && \
	docker compose run --rm api-php-cli ./vendor/bin/codecept build && \
	docker compose run --rm api-php-cli ./vendor/bin/codecept run --env=prod -- StartUp

test-hello-world:
	docker compose run --rm api-php-cli composer test -- tests/Acceptance/HelloWorldCest.php

test-api-not-found:
	docker compose run --rm api-php-cli composer test -- tests/Api/NotFoundCest.php && \
	make load-fixtures

test-api-auth-sign-up:
	docker compose run --rm api-php-cli composer test -- tests/Api/V1/Auth/SignUpCest.php && \
	make load-fixtures

test-api-auth-sign-up-expired-token:
	docker compose run --rm api-php-cli composer test -- tests/Api/V1/Auth/SignUpCest.php::testExpireToken

test-api-auth-confirm-email:
	docker compose run --rm api-php-cli composer test -- tests/Api/V1/Auth/ConfirmEmailCest.php && \
	make load-fixtures

test-api-auth-confirm-email-success:
	docker compose run --rm api-php-cli composer test -- tests/Api/V1/Auth/ConfirmEmailCest.php::testSuccess && \
	make load-fixtures

test-api-auth-login:
	docker compose run --rm api-php-cli composer test -- tests/Api/V1/Auth/SignInCest.php && \
	make load-fixtures

test-api-auth-login-success:
	docker compose run --rm api-php-cli composer test -- tests/Api/V1/Auth/SignInCest.php::testSuccess && \
	make load-fixtures

test-api-auth-sign-out:
	docker compose run --rm api-php-cli composer test -- tests/Api/V1/Auth/SignOutCest.php && \
	make load-fixtures

test-api-user-info:
	docker compose run --rm api-php-cli composer test -- tests/Api/V1/User/InfoCest.php && \
	make load-fixtures

test-unit:
	docker compose run --rm api-php-cli composer test -- Unit

test-coverage: test-coverage-clear
	docker compose run --rm api-php-cli composer test -- Unit --coverage --coverage-html

test-coverage-clear:
	docker compose run --rm api-php-cli sh -c 'rm -rf var/.tests/*'

var-all:
	docker compose run --rm api-php-cli sh -c 'chmod 777 var'

console-symlink-create: console-symlink-clear
	docker compose run --rm api-php-cli ln -s ./cli/app.php ./app

console-symlink-clear:
	docker compose run --rm api-php-cli rm -f ./app

generate-api:
	docker compose run api-php-cli ./vendor/bin/openapi . -o web/assets/openapi.json -f json -e vendor -e tests -e .docker -e var -e templates -e cli -e web -e config -e fixtures

wait-db:
	docker compose run api-php-cli /usr/local/bin/wait-for-it.sh db-postgres:5432

validate-schema:
	docker compose run api-php-cli php app orm:validate-schema

migrate:
	docker compose run api-php-cli php app migrations:migrate --no-interaction

migrate-generate:
	docker compose run api-php-cli php app migrations:diff --no-interaction

migrate-generate-empty:
	docker compose run api-php-cli php app migrations:generate --no-interaction

load-fixtures:
	docker compose run api-php-cli php app fixtures:load

env:
	docker compose run api-php-cli php vendor/bin/codecept g:env prod
