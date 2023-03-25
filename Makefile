init: docker-down-clear \
	docker-pull docker-build docker-up

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

docker-try-build-prod:
	docker-compose -f docker-compose-prod.yml up -d

lint:
	docker-compose run --rm api-php-cli composer lint && docker-compose run --rm api-php-cli composer cs-check

analyze:
	docker-compose run --rm api-php-cli composer psalm && docker-compose run --rm api-php-cli composer phpstan
