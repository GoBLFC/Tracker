While the Docker containers are generally easier to setup and use, manual setup of Tracker isn't terribly complex either.
This gives you the most control over every aspect of the setup and lets you interact directly with the system without any layers of abstraction that some may find undesireable.
You'll also be able to run Tracker on a traditional shared web host this way.

## Requirements

-   A web server, such as Nginx or Apache
-   PostgreSQL, MariaDB, or MySQL (8.0+) server as a database
-   _(recommended)_ Redis, Memcached, or another Laravel-compatible key/value store for caching data
-   PHP 8.2+ with the following extensions:
    -   PDO + your database extension of choice
    -   openssl
    -   ctype
    -   filter
    -   hash
    -   mbstring
    -   session
    -   tokenizer
    -   ZIP
    -   GD
-   [Composer](https://getcomposer.org) to install backend dependencies
-   [Node.js](https://nodejs.org) & NPM to install frontend dependencies
-   Your event's instance of [ConCat](https://concat.app) to allow volunteers and staff to authenticate with the system
    -   All users log in to the application using ConCat with OAuth.
    -   You will need Developer access to your event's ConCat instance to create the OAuth app.
        Specifically, you will need the `oauth:manage` permission.
        Alternatively, have someone else create an OAuth app in ConCat for you and have them provide you the client ID and secret.
    -   The OAuth app itself will require the `volunteer:read` and `registration:read` application permissions for OAuth Bearer tokens, which are used for generating the Volunteer Applications reports and retrieving badge details inside Tracker.

## Installation

1. Clone this repository in your web server's document root (or download a tarball and extract it to it).
1. Run `composer install --no-dev --classmap-authoritative` to download all production backend dependencies and optimize the autoloader automatically.
1. Run `npm install` to download all frontend dependencies.
1. Run `npm run build` to bundle and optimize the frontend assets.
1. Copy `.env.example` to `.env` and update the values appropriately.
1. Run `php artisan key:generate` to generate an encryption key and automatically fill in the `APP_KEY` value in `.env`.
   This key should be kept the same between all instances of Tracker connected to the same environment (production, QA, etc.) and should only be regenerated when absolutely necessary (compromise, improved algorithm).
   Regenerating or using a different key will result in any encrypted (not hashed!) values in the database or cache becoming unreadable.
1. Run `php artisan migrate` to run all migrations on the database.
1. Log in to the application in your web browser via the OAuth flow to make sure a user gets created for you.
1. Run `php artisan auth:set-role` to set your user's role to admin.
1. Run `php artisan telegram:set-commands` to send the list of bot commands to Telegram.
1. Run `php artisan telegram:set-webhook` to inform Telegram of the bot's webhook URL.
1. Add a cron entry to run `php artisan schedule:run` every minute so that reward notifications can be triggered and ongoing shifts automatically stopped at the configured day boundary.
    - Example crontab entry: `* * * * * cd /var/www/html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1'`
1. Run `php artisan queue:work` in a separate process (using [supervisor](http://supervisord.org) or something similar) to process queue entries as they come in.
   You can have multiple of these running at once if the queue becomes backed up.
1. To greatly improve boot performance of the application on each hit, run the following:
    - `php artisan config:cache` to cache the fully-resolved configuration to a file
    - `php artisan route:cache` to cache the routes to a file
    - `php artisan event:cache` to cache the auto-discovered event listeners to a file
    - `php artisan view:cache` to pre-compile and cache all of the Blade templates

## Updating

1. Run `php artisan down` to put the application in maintenace mode.
1. Pull or upload the current version of the code from this repository.
1. Run `composer install --no-dev --classmap-authoritative` to download any new production backend dependencies and optimize the autoloader automatically.
1. Run `npm install` to download any new frontend dependencies.
1. Run `npm run build` to bundle and optimize the frontend assets.
1. Run `php artisan migrate` to run any new migrations on the database.
1. Run `php artisan telegram:set-commands` to send the list of bot commands to Telegram.
1. Run `php artisan telegram:set-webhook` to inform Telegram of the bot's webhook URL.
1. Restart any queue workers you have running (`php artisan queue:work`) in separate processes to ensure they're using the latest code.
1. To greatly improve boot performance of the application on each hit, run the following:
    - `php artisan config:cache` to cache the fully-resolved configuration to a file
    - `php artisan route:cache` to cache the routes to a file
    - `php artisan event:cache` to cache the auto-discovered event listeners to a file
    - `php artisan view:cache` to pre-compile and cache all of the Blade templates
1. Run `php artisan up` to pull the application out of maintenace mode.
