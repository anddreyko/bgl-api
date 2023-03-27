init: docker-down-clear \
	docker-pull docker-build docker-up \
	console-symlink-create test-start-up

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

docker-try-build-prod: docker-down-clear
	docker-compose -f docker-compose-prod.yml up -d && make test-acceptance-fast

check: lint analyze test

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
	docker-compose run --rm api-php-cli composer test

test-acceptance:
	docker-compose run --rm api-php-cli composer test -- Acceptance

test-start-up:
	docker-compose run --rm api-php-cli composer test -- StartUp

test-unit:
	docker-compose run --rm api-php-cli composer test -- Unit

test-coverage: test-coverage-clear
	docker-compose run --rm api-php-cli composer test -- Unit --coverage --coverage-html

test-coverage-clear:
	docker-compose run --rm api-php-cli sh -c 'rm -rf var/.tests/*'

var-all:
	docker-compose run --rm api-php-cli sh -c 'chmod 777 var'

console-symlink-create: console-symlink-clear
	docker-compose run --rm api-php-cli ln -s ./console/app.php ./app

console-symlink-clear:
	docker-compose run --rm api-php-cli rm -f ./app

generate-api:
	docker-compose run api-php-cli ./vendor/bin/openapi ./src -o web/assets/openapi.json -f json
