#!/bin/sh

cd "$(dirname "$0")/.."
git pull
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data /var/www/html/storage
docker compose -f docker-compose.prod.yml exec app php artisan down
docker compose -f docker-compose.prod.yml exec app php artisan migrate
docker compose -f docker-compose.prod.yml exec app php artisan up
