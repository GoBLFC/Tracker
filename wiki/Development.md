The development environment uses [Laravel Sail](https://laravel.com/docs/11.x/sail), a containerized environment with a script and easy-to-use commands for interacting with it.

## Prerequisites

-   **All platforms:** [Docker](https://www.docker.com/)
-   **Windows:** Enable [WSL 2](https://learn.microsoft.com/en-us/windows/wsl/install) with a Linux distro. Run all commands in WSL.

## Configuration

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

After doing so, update the values only as needed.
The important ones that will most likely need to be filled in are the ConCat and Telegram items.

### ConCat

1. Create a ConCat account on your ConCat instance for OAuth and ensure it has developer authorization.
1. Add a new OAuth App at **Housekeeping** -> **Developers** -> **OAuth Applications** -> **Create New**
    - Use `http://localhost` for the callback URL
    - Select the `registration:read` and `volunteer:read` application permissions
1. Update `CONCAT_CLIENT_SECRET` and `CONCAT_CLIENT_ID` in `.env`

### Telegram Bot

1. Create a bot via [@BotFather](https://t.me/botfather) (`/newbot`)
1. Update `TELEGRAM_BOT_TOKEN` in `.env`

## Sail Setup

1. Install the PHP CLI for your environment (ex: `sudo apt install php-cli`)
1. Install [Composer](https://getcomposer.org/download/)
1. Run `composer install` in the application directory
1. _(Optional)_ Add `sail` alias with `alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'`
    - It would probably be a good idea to add this to your shell startup script (ex: `~/.bashrc`)

## Running the Developer Environment

Whenever using a Sail command, if you don't have an alias setup, use `sh vendor/bin/sail` instead of `sail`.

1. To run the container, use `sail up` _(Ctrl+C to stop)_
1. _If this is the first time the env has been run:_
    1. Run `sail artisan key:generate` (Updates `APP_KEY` in `.env`)
    1. Run `sail npm install`
    1. Initialize the database schema with `sail artisan migrate`
    1. _(Optional)_ Seed the database with dummy accounts with `sail artisan db:seed`
1. To run the Vite server, use `sail npm run dev` _(Ctrl+C to stop)_
1. Open the project on [http://localhost](http://localhost) (it may be slow)
    - If you see an Apache/nginx/etc. splash screen, ensure you don't already have a web server bound to port 80.

## Development Tips

-   Using `php artisan tinker` or `sail artisan tinker` will present a PHP REPL with the application bootstrapped, allowing you to mess with any part of the application and see the result of code in real-time. See [the Artisan documentation](https://laravel.com/docs/11.x/artisan#tinker) for more information.
-   The helpers `dump(...)` and `dd(...)` can be extremely helpful for debugging the application. The former pretty-prints a representation of any data passed to it with full HTML formatting, and the latter does the same but also immediately halts further execution of the application. Collections and Carbon instances also have `->dump()` and `->dd()` methods.
-   Use `php artisan make:migration` or `sail artisan make:migration` to create a new database migration. See [the migrations documentation](https://laravel.com/docs/11.x/migrations) for more information.
-   The Laravel [documentation](https://laravel.com/docs/11.x) and [API documentation](https://laravel.com/api/11.x/) will be very helpful if you're not already familiar with the framework.
-   Running `composer run format` and `npm run format` will format all PHP and JavaScript code, respectively.
-   Running `npm run lint` will lint all JavaScript code, checking for common errors and making recommendations.
    -   `npm run lint:fix` will automatically apply fixes for many of these.
    -   `npm run lint:fix-unsafe` will correct even more, but these changes should be manually verified.

## Building & testing the production images

1. Clone this repository whereever you will be building/running the Docker containers
1. Copy all of the `*.env.example` files in `.docker/` to just `*.env` (in the same directory).
   `.docker/` should have `app.env`, `certbot.env`, `nginx.env`, `postgres.env`, and `redis.env`.
1. Modify the `.env` files in `.docker/` for your configuration.
   Specifically, you should ensure the following keys are updated at the very least:
    - **app.env:** `APP_KEY`, `APP_URL`, `DB_PASSWORD`, `REDIS_PASSWORD`, `CONCAT_CLIENT_ID`, `CONCAT_CLIENT_SECRET`, `TELEGRAM_BOT_TOKEN` (see details in the development section for the ConCat and Telegram config keys, as well as for generating a key for `APP_KEY` the first time)
        - For the `APP_KEY` value, this is the encryption key used for all reversible encryption in the application.
          This is typically generated by the `php artisan key:generate` command, but running this requires PHP setup locally, which may not always be desireable.
          The key itself is a base64-encoded string of 32 bytes of entirely random data when using the AES-256-CBC cipher (default).
          You can easily generate this yourself
    - **certbot.env:** `LETSENCRYPT_DOMAINS`, `LETSENCRYPT_EMAIL`, `LETSENCRYPT_DRY_RUN` (clear the value for this once confirmed working)
    - **nginx.env:** `NGINX_HOST`
    - **postgres.env:** `POSTGRES_PASSWORD` (should match `DB_PASSWORD` in `app.env`)
    - **redis.env:** `REDIS_PASSWORD` (should match `REDIS_PASSWORD` in `app.env`)
1. Run `docker compose -f docker-compose.prod.yml build` to build the necessary (app and nginx) images
1. Run `docker compose -f docker-compose.prod.yml up -d` to run the images in the Docker daemon
1. Once everything has started up, the application will not yet be functional if it's the first time running.
   Follow these steps once the containers are up:
    1. Run `docker compose -f docker-compose.prod.yml exec app php artisan migrate` to run migrations on the database
    1. Wait for output from certbot in `docker compose -f docker-compose.prod.yml logs certbot` to confirm that the dry-run succeeded
    1. Clear the value for `LETSENCRYPT_DRY_RUN` in certbot.env
    1. Run `docker compose -f docker-compose.prod.yml restart certbot`
    1. Log in to Tracker to make sure a user is created for you
    1. Run `docker compose -f docker-compose.prod.yml exec app php artisan auth:set-role` to set your user's role to admin
