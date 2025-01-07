Docker is recommended to run Tracker, as a set of official container images are already built to make the process as easy as possible.
This removes/simplifies a lot of effort, both when setting everything up initially, and when updating to a newer version.

## Requirements

-   [Docker](https://docs.docker.com/) (or [Podman](https://podman.io/) with some form of Compose installed)
-   Your event's instance of [ConCat](https://concat.app) to allow volunteers and staff to authenticate with the system
    -   All users log in to the application using ConCat with OAuth.
    -   You will need Developer access to your event's ConCat instance to create the OAuth app.
        Specifically, you will need the `oauth:manage` permission to set everything up.
        Alternatively, have someone else create an OAuth app in ConCat for you and have them provide you with the client ID and secret.
    -   The OAuth app will require the `volunteer:read` and `registration:read` application permissions for OAuth Bearer tokens, which are used for generating the Volunteer Applications reports and retrieving badge details inside Tracker.

## Installation

### Official container images

Certbot-based Let's Encrypt automatic SSL renewal support is provided out-of-the-box when using these together.
It's certainly not mandatory to use them all, though - the main image is just the application layer and can be combined with any FastCGI-compatible web server.

Details on each individual image can be seen on their corresponding pages.

-   [goblfc/tracker](https://github.com/GoBLFC/Tracker/pkgs/container/tracker):
    Main application service running PHP-FPM, cron, a queue worker, and Telegram integrations
-   [goblfc/tracker-nginx](https://github.com/GoBLFC/Tracker/pkgs/container/tracker-nginx):
    Nginx web server configured for specific use with Tracker and its static assets
-   [goblfc/tracker-certbot](https://github.com/GoBLFC/Tracker/pkgs/container/tracker-certbot):
    Certbot runner to auto-install and auto-renew SSL certificates for the Nginx server

### Compose setup

The simplest way to run Tracker's Docker containers is to use [Docker Compose](https://docs.docker.com/compose/).
With a single Compose file, you can configure and control all of the necessary containers to run Tracker in one spot.

1. Create a `docker-compose.yml` file from the example below
1. Adjust all placeholder configuration values in each containers' `env` keys.
   The critical ones that absolutely _must_ be changed for each container are below.
    - **App:** Main application services container for Tracker, where all of the actual code runs
        - `APP_KEY` (see [Generating APP_KEY](#generating-app_key))
        - `APP_URL` (full URL to Tracker without a trailing slash)
        - `DB_PASSWORD` (choose a long random password)
        - `REDIS_PASSWORD` (choose a long random password, different from the DB password)
        - `CONCAT_BASE_URI` (full URL to your ConCat instance without a trailing slash)
        - `CONCAT_CLIENT_ID` (see [ConCat setup](#concat-setup))
        - `CONCAT_CLIENT_SECRET` (see [ConCat setup](#concat-setup))
        - `TELEGRAM_BOT_TOKEN` (see [Telegram setup](#telegram-setup))
    - **Postgres:** All persistent data is stored in the PostgreSQL database running here
        - `POSTGRES_PASSWORD` (must match `DB_PASSWORD` on the Tracker container)
    - **Redis:** Ephemeral data is stored in the Redis cache running here
        - `REDIS_PASSWORD` (must match `REDIS_PASSWORD` on the Tracker container)
    - **Nginx:** Web server/reverse proxy that serves static files and forwards relevant requests to the app
        - `NGINX_HOST` (the plain domain name that you're using for Tracker)
    - **Certbot:** Handles automatic SSL certificate issuance and renewal
        - `LETSENCRYPT_DOMAIN` (must match `NGINX_HOST` on the Nginx container)
        - `LETSENCRYPT_EMAIL` (email address to associate with the issued certificates, used for notifications of problems)
1. Start the containers in the background (in the Docker daemon) by running `docker compose up -d` in the directory that the Compose file is in
1. Once everything has started up, the application won't yet be functional if it's the first time it has been run.
   Follow these steps once the containers are up:
    1. Run `docker compose exec app php artisan migrate` to run migrations on the database
    1. Wait for output from certbot in `docker compose logs certbot` to confirm that the dry-run succeeded.
       If this fails, then something is likely wrong with the configuration.
       Double-check the values for `NGINX_HOST` and `LETSENCRYPT_DOMAIN`.
       You can restart the certbot and nginx containers after modifying their configuration with `docker compose restart certbot` and `docker compose restart nginx`.
       Only move on to the next step once the dry-run has succeeded.
    1. Clear the value for `CERTBOT_DRY_RUN` in `docker-compose.yml` (make its value blank, nothing after the `:`)
    1. Restart the certbot container with `docker compose restart certbot`.
       Wait 30 seconds and check Certbot's log output again (`docker compose logs certbot`) to confirm that the actual certificate issuance succeeded.
    1. Restart the nginx container with `docker compose restart nginx`.
       This will force it to pick up the new certificate immediately.
    1. Log in to Tracker to verify your ConCat authentication is working and to ensure a user account is made for you
    1. Run `docker compose exec app php artisan auth:set-role` to set your user's role to admin
1. Congratulations, you've set up Tracker!

#### Example `docker-compose.yml`

```yaml
name: tracker
services:
    app:
        image: "ghcr.io/goblfc/tracker:v3"
        restart: always
        env:
            APP_NAME: BLFC Tracker
            APP_ENV: production
            APP_KEY: "base64:<randomly-generated app key>"
            APP_DEBUG: false
            APP_URL: https://tracker.test
            LOG_CHANNEL: stack
            LOG_STACK: daily
            LOG_LEVEL: info
            DB_CONNECTION: pgsql
            DB_HOST: postgres
            DB_PORT: 5432
            DB_DATABASE: tracker
            DB_USERNAME: tracker
            DB_PASSWORD: "<some secure password>"
            SESSION_DRIVER: redis
            SESSION_LIFETIME: 60
            SESSION_ENCRYPT: false
            SESSION_PATH: /
            SESSION_DOMAIN: null
            QUEUE_CONNECTION: redis
            CACHE_STORE: redis
            REDIS_HOST: redis
            REDIS_PORT: 6379
            REDIS_PASSWORD: "<some secure password>"
            CONCAT_BASE_URI: https://reg.gobltc.com
            CONCAT_CLIENT_ID: "<ConCat client ID>"
            CONCAT_CLIENT_SECRET: "<ConCat client secret>"
            TELEGRAM_BOT_TOKEN: "<Telegram bot token>"
            TRACKER_TIMEZONE: America/Los_Angeles
        volumes:
            - app-logs:/var/www/html/storage/logs
        depends_on:
            - postgres
            - redis

    postgres:
        image: postgres:17
        restart: always
        env:
            POSTGRES_DB: tracker
            POSTGRES_USER: tracker
            POSTGRES_PASSWORD: "<some secure password>"
        volumes:
            - postgres:/var/lib/postgresql/data

    redis:
        image: redis:7
        command:
            - /bin/sh
            - -c
            - redis-server --save 60 1 --loglevel warning --requirepass "$${REDIS_PASSWORD:?REDIS_PASSWORD not provided}"
        restart: always
        env:
            REDIS_PASSWORD: "<some secure password>"
        volumes:
            - redis:/data

    nginx:
        image: "ghcr.io/goblfc/tracker-nginx:v3"
        restart: always
        env:
            NGINX_HOST: tracker.test
            NGINX_HTTP_PORT: 80
            NGINX_HTTPS_PORT: 443
            NGINX_EXTERNAL_HTTP_PORT: 80
            NGINX_EXTERNAL_HTTPS_PORT: 443
        ports:
            - "80:80"
            - "443:443"
        volumes:
            - certbot-conf:/etc/letsencrypt
            - certbot-www:/var/www/certbot
        depends_on:
            - app

    certbot:
        image: "ghcr.io/goblfc/tracker-certbot:v3"
        restart: unless-stopped
        env:
            LETSENCRYPT_DOMAIN: tracker.test
            LETSENCRYPT_EMAIL: youremail@yourcon.org
            LETSENCRYPT_DRY_RUN: true
        volumes:
            - certbot-conf:/etc/letsencrypt
            - certbot-www:/var/www/certbot
        depends_on:
            - nginx

volumes:
    app-logs:
    postgres:
    redis:
    certbot-conf:
    certbot-www:
```

### Generating APP_KEY

The `APP_KEY` configuration value is the encryption key that the application will use for all reversible encryption.
This should be rotated if it is ever exposed.
Generating this key is typically done by running `php artisan key:generate`, but this requires PHP to be setup locally, which isn't always desireable.
When using the AES-256-CBC cipher (which is the default), the key itself is simply a base64-encoded string of 32 bytes of entirely random data, prefixed with `base64:`.
This can easily be generated without Artisan/PHP, such as with the below command:

```sh
echo "base64:$(head -c 32 /dev/random | base64)"
```

The resulting value should look something like `base64:QSiqblPkv+xnBBhP4Y3rvC4gtyYkGqCfzxuQFGGOPY4=` (don't use this one!), which you can copy and paste into the `APP_KEY` value.

### ConCat setup

1. Log in to a ConCat account that has developer authorization
1. Add a new OAuth App at **Housekeeping** -> **Developers** -> **OAuth Applications** -> **Create New**
    - Use `https://your-tracker.domain` for the callback URL
    - Select the `registration:read` and `volunteer:read` application permissions
1. Use the provided client ID and secret for the `CONCAT_CLIENT_SECRET` and `CONCAT_CLIENT_ID` environment variables

### Telegram setup

1. Create a bot via [@BotFather](https://t.me/botfather) (`/newbot`)
1. Use the bot token it gives you for the `TELEGRAM_BOT_TOKEN` environment variable

## Updating

A convenient update script is provided at [.docker/scripts/update.sh](../.docker/scripts/update.sh).
In this order, that script does the following:

1. Pulls the latest container images (`docker compose pull`)
1. Enables Laravel's maintenance mode (`docker compose exec app php artisan down`)
1. Restarts the containers with the updated images (`docker compose down && docker compose up -d`)
1. Ensures the log volume's file permissions remain consistent (`docker compose exec app chown -R www-data:www-data /var/www/html/storage`)
1. Runs any new database migrations (`docker compose exec app php artisan migrate`)
1. Disables Laravel's maintenance mode (`docker compose exec app php artisan up`)

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

1. Run [`.docker/scripts/postgres-dump.sh`](../.docker/scripts/postgres-dump.sh) to dump the contents of the database to `pgdump.sql` in the project directory
1. Stop the Postgres container (`docker compose stop postgres`)
1. Find the correct volume for the Postgres container in `docker volume ls` - it should be something like `projectname_postgres`
1. Delete the volume of the Postgres container (`docker volume rm postgres_volume_name`)
1. Start the Postgres container (`docker compose start postgres`)
1. Run [`.docker/scripts/postgres-restore.sh`](../.docker/scripts/postgres-restore.sh) to import `pgdump.sql` into the new Postgres version
1. Verify that the application is running properly and all of the correct data is in place
