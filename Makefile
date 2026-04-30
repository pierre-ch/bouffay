up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

bash:
	docker compose exec php sh

composer-install:
	docker compose exec php composer install

symfony-new:
	docker compose exec php composer create-project symfony/skeleton:"6.4.*" . --no-interaction

console:
	docker compose exec php php bin/console $(cmd)

cache-clear:
	docker compose exec php php bin/console cache:clear

logs:
	docker compose logs -f
