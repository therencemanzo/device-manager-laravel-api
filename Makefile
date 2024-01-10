db-fresh:
	@make migrate
	@make seed

migrate:
	php artisan migrate:fresh
seed:
	php artisan db:seed