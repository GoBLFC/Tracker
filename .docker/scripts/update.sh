#!/bin/sh

cd "$(dirname "$0")/../.."
docker compose pull
docker compose exec app php artisan down
docker compose down
docker compose up -d
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app php artisan migrate
docker compose exec app php artisan up
