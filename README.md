# BLFC Volunteer Shift Tracker System
Tracker is an at-con time clock system for volunteers at [BLFC](https://goblfc.org).
Volunteers can clock in and clock out for their shifts to log their hours, ensuring they get the rewards they deserve.
Staff can manage volunteers and run reports on volunteer hours.

## Installation
### Docker
Docker is recommended to run Tracker, as a Docker Compose file and corresponding Dockerfiles are already built to make the process as easy as possible.
Certbot-based Let's Encrypt automatic SSL renewal support is provided out-of-the-box with the default production Compose file.

1. Clone this repository whereever you will be building/running the Docker containers
1. Copy all of the `*.env.example` files in `.docker/env` to just `*.env` (in the same directory).
	`.docker/env` should have `app.env`, `certbot.env`, `nginx.env`, `postgres.env`, and `redis.env`.
1. Modify the `.env` files in `.docker/env` for your configuration.
	Specifically, you should ensure the following keys are updated at least:
	- `app.env`: `APP_KEY`, `APP_URL`, `DB_PASSWORD`, `REDIS_PASSWORD`, `CONCAT_CLIENT_ID`, `CONCAT_CLIENT_SECRET`, `TELEGRAM_BOT_TOKEN` (see details in the manual section for the ConCat and Telegram config keys, as well as for generating a key for `APP_KEY`)
	- `certbot.env`: `LETSENCRYPT_DOMAIN`, `LETSENCRYPT_EMAIL`, `LETSENCRYPT_DRY_RUN` (clear the value for this once confirmed working)
	- `nginx.env`: `NGINX_HOST`
	- `postgres.env`: `POSTGRES_PASSWORD` (should match `DB_PASSWORD` in `app.env`)
	- `redis.env`: `REDIS_PASSWORD` (should match `REDIS_PASSWORD` in `app.env`)
1. Run `docker compose -f docker-compose.prod.yml build` to build the necessary (app and nginx) images
1. Run `REDIS_PASSWORD=<redis password here> docker compose -f docker-compose.prod.yml up -d` to run the images in the Docker daemon
	- Defining `REDIS_PASSWORD` is sadly currently required to start the Redis container properly due to the way the variable is obtained
1. Once everything has started up, the application will not yet be functional if it's the first time running.
	Follow these steps once the containers are up:
	1. Run `docker compose -f docker-compose.prod.yml exec app php artisan migrate` to run migrations on the database. This will need to be done whenever updating Tracker (if there are new migrations in the update).
	1. Wait for output from certbot in `docker compose -f docker-compose.prod.yml logs certbot` to confirm that the dry-run succeeded
	1. Clear the value for `CERTBOT_DRY_RUN` in `certbot.env`
	1. Run `docker compose -f docker-compose.prod.yml restart certbot`
	1. Log in to Tracker to make sure a user is created for you
	1. Run `docker compose -f docker-compose.prod.yml exec app php artisan auth:set-role` to set your user's role to admin

### Manual
#### Requirements
- A web server, such as Nginx or Apache
- PHP 8.1+ with the following extensions:
	* PDO + your database extension of choice
	* openssl
	* ctype
	* filter
	* hash
	* mbstring
	* session
	* tokenizer
	* ZIP
	* GD
- [Composer](https://getcomposer.org)
- [Node.js](https://nodejs.org) & NPM
- PostgreSQL, MariaDB, or MySQL (8.0+) server
- Your event's instance of [ConCat](https://concat.app) to allow volunteers and staff to authenticate with the system
	* All users log in to the application using ConCat with OAuth.
	* You will need Developer access to your event's ConCat instance to create the OAuth app.
	  Specifically, you will need the `oauth:manage` permission.
	  Alternatively, have someone else create an OAuth app in ConCat for you and have them provide you the client ID and secret.
	* The OAuth app itself will require the `volunteer:read` and `registration:read` application permissions for OAuth Bearer tokens, which are used for generating the Volunteer Applications reports and retrieving badge details inside Tracker.

#### Procedure
1. Clone this repository in your web server's document root (or download a tarball and extract it to it).
1. Run `composer install --no-dev --classmap-authoritative` to download all production backend dependencies and optimize the autoloader automatically.
1. Run `npm install` to download all frontend dependencies.
1. Run `npm run build` to bundle and optimize the frontend assets.
1. Copy `.env.example` to `.env` and update the values appropriately.
1. Run `php artisan key:generate` to generate an encryption key and automatically fill in the `APP_KEY` value in `.env`.
	This key should be kept the same between all instances of Tracker connected to the same environment (production, QA, etc.) and should only be regenerated when absolutely necessary (compromise, improved algorithm).
	Regenerating or using a different key will result in any encrypted (not hashed!) values in the database or cache becoming unreadable.
1. Run `php artisan migrate` to run migrations on the database. This will need to be done whenever updating Tracker (if there are new migrations in the update).
1. Log in to Tracker to make sure a user is created for you.
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
	- `php artisan event:cache` to cache the auto-discovered event listeners to a file (not used at the moment)
	- `php artisan view:cache` to pre-compile and cache all of the Blade templates

## Development
### Architecture Overview

### Developer Setup

For a container based dev environment

#### Pre-requisites

* **All:** [Docker](https://www.docker.com/)
* **Windows Only:** Enable [WSL 2](https://learn.microsoft.com/en-us/windows/wsl/install) with a linux distro. Run all commands in WSL.

#### .Env Config

```bash
cp .env.example .env
```

##### ConCat

1. Get a [test concat](https://reg.gobltc.com) account for OAuth and get developer authorization from Glitch, Levi, or Gawdl3y .
1. Add a new OAuth App at `Housekeeping` -> `Developers` -> `OAuth Applications` -> `Create New` with `http://localhost` as the callback URI, and the `registration:read` and `volunteer:read` application permissions.
1. Update `CONCAT_CLIENT_SECRET` and `CONCAT_CLIENT_ID` in `.env`

##### Telegram Bot

 1. Create a bot via [@BotFather](https://t.me/botfather) (`/newbot`)
 1. Update `TELEGRAM_BOT_TOKEN` in `.env`

##### [Sail](https://laravel.com/docs/10.x/sail) Setup (Containerized build environment)

 1. Install the PHP CLI for your environment (ex: `sudo apt install php-cli`)
 1. Install [Composer](https://getcomposer.org/download/)
 1. `composer install`
 1. _(Optional)_ Add `sail` alias `alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'`, possibly to your shell startup (ex: `~/.bashrc`)

##### Running the Developer Environment

 1. `sail up` _(Ctrl+C to stop)_
 1. _If this is the first time the env has been run:_
    1. `sail artisan key:generate` (Updates `APP_KEY` in `.env`)
    1. `sail npm install`
 1. `sail npm run dev` _(Ctrl+C to stop)_
 1. _If this is the first time the env has been run:_
    1. Initialize the database schema `sail artisan migrate`
    1. _(Optional)_ Seed the database with dummy accounts `sail artisan db:seed`
 1. Open the project on [http://localhost](http://localhost) (it may be slow)
    * If you see an apache splash screen, apache2 instance is already bound to 80.

### Glossary

* [Laravel](https://laravel.com/) - PHP web server framework
* [Sail](https://laravel.com/docs/10.x/sail) - Laravel system that manages a developer container, proxying commands into the container
* [Artisan](https://laravel.com/docs/10.x/artisan) - Laravel command line helper application (Used to run Sail)
* [Vite](https://vitejs.dev/) - Asset bundling and cache-breaking for the js and image assets
* [Blade](https://laravel.com/docs/10.x/blade) - Laravel template engine.


# Laravel
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.
