Docker is recommended to run Tracker, as a Docker Compose file and corresponding Dockerfiles are already built to make the process as easy as possible.
Certbot-based Let's Encrypt automatic SSL renewal support is provided out-of-the-box with the default production Compose file.

## Installation

1. Clone this repository whereever you will be building/running the Docker containers
1. Copy all of the `*.env.example` files in `.docker/` to just `*.env` (in the same directory).
   `.docker/` should have `app.env`, `certbot.env`, `nginx.env`, `postgres.env`, and `redis.env`.
1. Modify the `.env` files in `.docker/` for your configuration.
   Specifically, you should ensure the following keys are updated at the very least:
    - **app.env:** `APP_KEY`, `APP_URL`, `DB_PASSWORD`, `REDIS_PASSWORD`, `CONCAT_CLIENT_ID`, `CONCAT_CLIENT_SECRET`, `TELEGRAM_BOT_TOKEN` (see details in the development section for the ConCat and Telegram config keys, as well as for generating a key for `APP_KEY` the first time)
    - **certbot.env:** `LETSENCRYPT_DOMAIN`, `LETSENCRYPT_EMAIL`, `LETSENCRYPT_DRY_RUN` (clear the value for this once confirmed working)
    - **nginx.env:** `NGINX_HOST`
    - **postgres.env:** `POSTGRES_PASSWORD` (should match `DB_PASSWORD` in `app.env`)
    - **redis.env:** `REDIS_PASSWORD` (should match `REDIS_PASSWORD` in `app.env`)
1. Run `docker compose -f docker-compose.prod.yml build` to build the necessary (app and nginx) images
1. Run `docker compose -f docker-compose.prod.yml up -d` to run the images in the Docker daemon
1. Once everything has started up, the application will not yet be functional if it's the first time running.
   Follow these steps once the containers are up:
    1. Run `docker compose -f docker-compose.prod.yml exec app php artisan migrate` to run migrations on the database
    1. Wait for output from certbot in `docker compose -f docker-compose.prod.yml logs certbot` to confirm that the dry-run succeeded
    1. Clear the value for `CERTBOT_DRY_RUN` in certbot.env
    1. Run `docker compose -f docker-compose.prod.yml restart certbot`
    1. Log in to Tracker to make sure a user is created for you
    1. Run `docker compose -f docker-compose.prod.yml exec app php artisan auth:set-role` to set your user's role to admin

## Updating

A convenient update script is provided at [/.docker/scripts/update.sh](/.docker/update.sh).
In this order, that script does the following:

1. Pulls the latest changes from the repository (`git pull`)
1. Builds the updated image (`docker compose -f docker-compose.prod.yml build`)
1. Restarts the containers with the updated image (`docker compose -f docker-compose.prod.yml down && docker compose -f docker-compose.prod.yml up -d`)
1. Ensures the log volume's file permissions remain consistent (`docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data /var/www/html/storage`)
1. Enables Laravel's maintenance mode (`docker compose -f docker-compose.prod.yml exec app php artisan down`)
1. Runs any new database migrations (`docker compose -f docker-compose.prod.yml exec app php artisan migrate`)
1. Disables Laravel's maintenace mode (`docker compose -f docker-compose.prod.yml exec app php artisan up`)

These steps can be completed manually if preferred or if any tweaking is desired.

### Postgres upgrades

Whenever the Postgres major version is updated in the Compose file, Postgres needs to be manually upgraded beforehand.
If not done, an error like this will likely be encountered at the startup of the Postgres container:

```log
FATAL:  database files are incompatible with server
DETAIL:  The data directory was initialized by PostgreSQL version 15, which is not compatible with this version 16.1 (Debian 16.1-1.pgdg120+1).
```

Reverting the project (or at least the Compose file) to the previous version should allow the server to start again.
Follow this procedure to properly upgrade the database (starting with the server running the old version):

1. Run `./docker/scripts/postgres-dump.sh` to dump the contents of the database to `pgdump.sql` in the project directory
1. Stop the Postgres container (`docker compose -f docker-compose.prod.yml stop postgres`)
1. Find the correct volume for the Postgres container in `docker volume ls` - it should be something like `projectname_postgres`
1. Delete the volume of the Postgres container (`docker volume rm postgres_volume_name`)
1. Start the Postgres container (`docker compose -f docker-compose.prod.yml start postgres`)
1. Run `./docker/scripts/postgres-restore.sh` to import `pgdump.sql` into the new Postgres version
1. Verify that the application is running properly and all of the correct data is in place
