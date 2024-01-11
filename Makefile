setup:
	@make cp-env
	@make composer-update
	@make sail-up

init:
	@make key
	@make db-migrate
	@make db-seed
	@make artisan-scribe
	@make artisan-test


cp-env:
	cp .env.example .env
composer-update:
	composer update
sail-up:
	./vendor/bin/sail up

key:
	./vendor/bin/sail artisan key:generate 
db-migrate:
	./vendor/bin/sail artisan migrate:fresh
php-serve:
	./vendor/bin/sail artisan serve
db-seed:
	./vendor/bin/sail artisan db:seed
npm-install:
	npm install
npm-run:
	npm run dev
artisan-scribe:
	./vendor/bin/sail artisan scribe:generate
artisan-queue:
	./vendor/bin/sail artisan queue:work
artisan-schedule:
	./vendor/bin/sail artisan schedule:work
artisan-test:
	./vendor/bin/sail test