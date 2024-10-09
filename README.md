# BLFC Volunteer Shift Tracker System
Tracker is an at-con time clock system for volunteers at [BLFC](https://goblfc.org).
Volunteers can clock in and clock out for their shifts to log their hours, ensuring they get the rewards they deserve.
Staff can manage volunteers and run reports on volunteer hours.

## Docker Setup
Docker is recommended to run Tracker, as a Docker Compose file and corresponding Dockerfiles are already built to make the process as easy as possible.
Certbot-based Let's Encrypt automatic SSL renewal support is provided out-of-the-box with the default production Compose file.

1. Clone this repository whereever you will be building/running the Docker containers
1. Copy all of the `*.env.example` files in `.docker/env` to just `*.env` (in the same directory).
	`.docker/env` should have `app.env`, `certbot.env`, `nginx.env`, `postgres.env`, and `redis.env`.
1. Modify the `.env` files in `.docker/env` for your configuration.
	Specifically, you should ensure the following keys are updated at the very least:
	- **app.env:** `APP_KEY`, `APP_URL`, `DB_PASSWORD`, `REDIS_PASSWORD`, `CONCAT_CLIENT_ID`, `CONCAT_CLIENT_SECRET`, `TELEGRAM_BOT_TOKEN` (see details in the development section for the ConCat and Telegram config keys, as well as for generating a key for `APP_KEY` the first time)
	- **certbot.env:** `LETSENCRYPT_DOMAIN`, `LETSENCRYPT_EMAIL`, `LETSENCRYPT_DRY_RUN` (clear the value for this once confirmed working)
	- **nginx.env:** `NGINX_HOST`
	- **postgres.env:** `POSTGRES_PASSWORD` (should match `DB_PASSWORD` in `app.env`)
	- **redis.env:** `REDIS_PASSWORD` (should match `REDIS_PASSWORD` in `app.env`)
1. Run `docker compose -f docker-compose.prod.yml build` to build the necessary (app and nginx) images
1. Run `REDIS_PASSWORD=<redis password here> docker compose -f docker-compose.prod.yml up -d` to run the images in the Docker daemon
	- Defining `REDIS_PASSWORD` is sadly currently required to start the Redis container properly due to the way the variable is obtained
1. Once everything has started up, the application will not yet be functional if it's the first time running.
	Follow these steps once the containers are up:
	1. Run `docker compose -f docker-compose.prod.yml exec app php artisan migrate` to run migrations on the database
	1. Wait for output from certbot in `docker compose -f docker-compose.prod.yml logs certbot` to confirm that the dry-run succeeded
	1. Clear the value for `CERTBOT_DRY_RUN` in certbot.env
	1. Run `docker compose -f docker-compose.prod.yml restart certbot`
	1. Log in to Tracker to make sure a user is created for you
	1. Run `docker compose -f docker-compose.prod.yml exec app php artisan auth:set-role` to set your user's role to admin

### Updating
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

#### Postgres upgrades
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

## Manual Setup
### Requirements
- A web server, such as Nginx or Apache
- PHP 8.2+ with the following extensions:
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
- [Composer](https://getcomposer.org) to install backend dependencies
- [Node.js](https://nodejs.org) & NPM to install frontend dependencies
- PostgreSQL, MariaDB, or MySQL (8.0+) server as a database
- Your event's instance of [ConCat](https://concat.app) to allow volunteers and staff to authenticate with the system
	* All users log in to the application using ConCat with OAuth.
	* You will need Developer access to your event's ConCat instance to create the OAuth app.
	  Specifically, you will need the `oauth:manage` permission.
	  Alternatively, have someone else create an OAuth app in ConCat for you and have them provide you the client ID and secret.
	* The OAuth app itself will require the `volunteer:read` and `registration:read` application permissions for OAuth Bearer tokens, which are used for generating the Volunteer Applications reports and retrieving badge details inside Tracker.

### Installation
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

### Updating
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

## Development Setup
The development environment uses [Laravel Sail](https://laravel.com/docs/11.x/sail), a containerized environment with a script and easy-to-use commands for interacting with it.

### Pre-requisites
- **All platforms:** [Docker](https://www.docker.com/)
- **Windows:** Enable [WSL 2](https://learn.microsoft.com/en-us/windows/wsl/install) with a Linux distro. Run all commands in WSL.

### .Env Config
Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

After doing so, update the values only as needed.
The important ones that will most likely need to be filled in are the ConCat and Telegram items. 

#### ConCat Config
1. Create a ConCat account on your ConCat instance for OAuth and ensure it has developer authorization.
1. Add a new OAuth App at `Housekeeping` -> `Developers` -> `OAuth Applications` -> `Create New`
	- Use `http://localhost` for the callback URL
	- Select the `registration:read` and `volunteer:read` application permissions
1. Update `CONCAT_CLIENT_SECRET` and `CONCAT_CLIENT_ID` in `.env`

#### Telegram Bot Config
1. Create a bot via [@BotFather](https://t.me/botfather) (`/newbot`)
1. Update `TELEGRAM_BOT_TOKEN` in `.env`

### Sail Setup
1. Install the PHP CLI for your environment (ex: `sudo apt install php-cli`)
1. Install [Composer](https://getcomposer.org/download/)
1. Run `composer install` in the application directory
1. _(Optional)_ Add `sail` alias with `alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'`
	- It would probably be a good idea to add this to your shell startup script (ex: `~/.bashrc`)

### Running the Developer Environment
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

### Development Tips
- Using `php artisan tinker` or `sail artisan tinker` will present a PHP REPL with the application bootstrapped, allowing you to mess with any part of the application and see the result of code in real-time. See [the Artisan documentation](https://laravel.com/docs/11.x/artisan#tinker) for more information.
- The helpers `dump(...)` and `dd(...)` can be extremely helpful for debugging the application. The former pretty-prints a representation of any data passed to it with full HTML formatting, and the latter does the same but also immediately halts further execution of the application. Collections and Carbon instances also have `->dump()` and `->dd()` methods.
- Use `php artisan make:migration` or `sail artisan make:migration` to create a new database migration. See [the migrations documentation](https://laravel.com/docs/11.x/migrations) for more information.
- The Laravel [documentation](https://laravel.com/docs/11.x) and [API documentation](https://laravel.com/api/11.x/) will be very helpful if you're not already familiar with the framework.
- Running `composer run format` and `npm run format` will format all PHP and JavaScript code, respectively.
- Running `npm run lint` will lint all JavaScript code, checking for common errors and making recommendations.
	* `npm run lint:fix` will automatically apply fixes for many of these.
	* `npm run lint:fix-unsafe` will correct even more, but these changes should be manually verified.

## Architecture Overview
Since Laravel is an MVC (Model, View, Controller) framework, that structure is generally adhered to.
[PSR-4](https://www.php-fig.org/psr/psr-4/) autoloading is in use, so as long as the namespace and class filesystem structure is followed, files don't need to be manually included/required.

### Frequently Used Directories
- Routes: [routes](/routes)
- Controllers: [app/Http/Controllers](/app/Http/Controllers)
- Models: [app/Models](/app/Models)
- Views: [resources/views](/resources/views)
- Artisan commands: [app/Console/Commands](/app/Console/Commands)
- Database migrations: [database/migrations](/database/migrations)
- Configuration: [config](/config)
- JavaScript assets: [resources/js](/resources/js)
- Style assets (Sass/SCSS): [resources/sass](/resources/sass)
- Image assets: [resources/img](/resources/img)

### Custom Artisan Commands
Use `php artisan help` or `sail artisan help` to view a list of all available commands, not just custom ones.  
Use `php artisan help <command name>` or `sail artisan help <command name>` to view detailed information for a specific command.

| Name                   | Description                                                                                                                                                                     |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| auth:set-role          | Sets the role for a user. If user isn't specified on the CLI, then you will be prompted to search for and select the appropriate user, as well as to select the role to assign. |
| auth:fetch-unknown     | Retrieves and populates information for any users with the `unknown` username (previously-created Users that information couldn't be retrieved from ConCat for at the time).    |
| tracker:notify-rewards | Sends notifications to users for rewards they are newly eligible to claim. Automatically called by the task scheduler every 5 minutes.                                          |
| tracker:stop-ongoing   | Stops all ongoing time entries for the active event. Automatically called by the task scheduler every day at the configured day boundary hour.                                  |
| telegram:set-commands  | Sends the list of commands to Telegram to display to users interacting with the bot.                                                                                            |
| telegram:set-webhook   | Sends the webhook URL to Telegram. Requires the application to be accessed via HTTPS.                                                                                           |
| telegram:poll          | Polls Telegram for updates (primarily for development use).                                                                                                                     |

### Database Models
All database models are using UUIDv7 for their primary key (`id` column).
Eloquent is being used heavily for nearly all database interactions.
Foreign key constraints are used in the database whenever possible to ensure referential integrity at every step of the process.

All models and their relationships are listed below, alongside a brief description of their purpose.

| Name        | Table         | Description                                                                                                                                                                                  |
| ----------- | ------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Activity    | activities    | Used for tracking events and changes to models for audit logging purposes. Belongs to a User via both subject and causer.                                                                    |
| AttendeeLog | attendee_logs | A log to enter users into. Used for tracking attendance to a panel or other type of event. Has many Users, with `type` (`attendee` or `gatekeeper`) on the pivot table. Belongs to an Event. |
| Department  | departments   | An organizational unit for staff/volunteers of a convention.                                                                                                                                 |
| Event       | events        | A single convention/other type of event that time is tracked for.                                                                                                                            |
| Kiosk       | kiosks        | A device that has been authorized to allow volunteers to enter time on. These devices keep a cookie with the session key to identify themselves.                                             |
| QuickCode   | quick_codes   | One-time-use sign-in codes for users. Expires 30 seconds after creation. Belongs to a User.                                                                                                  |
| Reward      | rewards       | Possible reward/goal for volunteers to thank them for their time once they reach a threshold. Belongs to an Event.                                                                           |
| RewardClaim | reward_claims | Rewards claimed by users. Belongs to a User and a Reward.                                                                                                                                    |
| Setting     | settings      | Application settings identified by a string and stored as JSON.                                                                                                                              |
| TimeBonus   | time_bonuses  | Time periods that grant bonus volunteer time credit while being worked within. Belongs to an Event and many Departments.                                                                     |
| TimeEntry   | time_entries  | Volunteer time clocked by users. Belongs to a User, a Department, and an Event.                                                                                                              |
| User        | users         | Any user of the application. Has a role (Banned, Attendee, Volunteer, Staff, Lead, Manager, Admin) that determines permissions.                                                              |

### Permissions
Permissions are implemented very simply at the moment.
Users have a single assigned Role value, which is just an enum (Banned, Attendee, Volunteer, Staff, Lead, Manager, Admin).
- Volunteer is the default role for users. They have time tracking capabilities, but not much else.
- Leads can authorize/deauthorize Kiosks.
- Managers can authorize/deauthorize Kiosks, view and manage volunteers' time entries, manage attendee log attendees and gatekeepers, and create users with a badge ID.
- Admins can do anything, but especially are responsible for general entity CRUD operations.
- Attendees are just users created for the purpose of being an entry in an attendee log. They are automatically "promoted" to Volunteer if they ever log in.
- Banned users are prevented from interacting with the application entirely beyond signing in.

### Time Tracking Details
Since the primary purpose of Tracker is to track the time that volunteers spend working shifts, a lot of care has been put in to how that time is kept.

#### Time Entries
In order for a volunteer to enter time, they must visit an authorized kiosk at the beginning and end of their shift.
When they clock in, they select a department the shift will be for, and a TimeEntry is created in the database with the current time as its `start` field and a null `stop` field.
Any TimeEntry with a null `stop` field is considered to be an "ongoing" entry.
An ongoing TimeEntry's duration is the amount of time between its `start` and the current time.
Upon clocking out, the ongoing TimeEntry is updated to fill in the `stop` field with the current time.
A volunteer may only ever have a single ongoing TimeEntry.

#### Time Bonuses
An admin can create multiple TimeBonuses for an Event that apply within a time period (between `start` and `stop`) to specific Departments.
Any TimeEntry that is assigned to a Department + Event with a TimeBonus and has its time range even partially within a TimeBonus period has the TimeBonus' multiplier applied to the amount of time that is within the bonus period.

Example scenario:
- A TimeBonus exists for departments "Crowd Control", "Operations", and "Art Gallery" for event "BLFC 2023", between the period of 2023-10-31 18:00 and 2023-10-31 20:00, with a multiplier of 2.
- A TimeEntry is entered for the "Operations" department and "BLFC 2023" event, starting at 2023-10-31 16:00 and ending at 2023-10-31 19:00

The TimeEntry's raw duration will be 3hrs, and its "earned time" (duration with bonuses) will be 4hrs.
This is because the TimeEntry contained 1hr of time within the bonus period, so that single hour is considered to be worth double time.

#### Auto-Stopping Ongoing Time Entries
Each day at the configured day boundary hour (see [config/tracker.php], default 04:00), the application has a scheduled task to automatically terminate any ongoing time entries.
This is to catch the cases where a volunteer forgets to clock out after their shift, thus leaving the time entry running perpetually.
When a time entry is terminated this way, its stop time is updated to be either 1hr after the start time or the current time, whichever is sooner.
A notification is sent that they're forced to acknowledge on the web page the next time they log in.

### Attendee Logs
Attendee logs are an entity used to track attendees for a scheduled event such as a panel or meetup.
They can have any number of users entered into them by badge ID, and they don't even require the users entered to be valid volunteers or staff.
Any number of Gatekeepers can also be added to them, who will all be able to view and manage the attendees that are logged, regardless of their own role.

### Telegram Bot
#### Account Linking
Users can link their Telegram account to the application by scanning a QR code on the web page or manually visiting the proper Telegram link generated on it.
The QR code simply directs them to the same aforementioned link, which should automatically open Telegram and send a `/start <setup key>` command to the bot.
Every user has a Telegram setup key that is unique to them and randomly generated upon creation.
When the bot receives the start command with their setup code, it stores the Telegram chat ID on their User and uses this for future communication.

#### Notifications
The bot will automatically send notifications to users with linked accounts for the following scenarios:
- Their earned time has reached a reward's hours threshold
- They have claimed a reward with a staff member
- Their time entry has been auto-stopped at the day boundary hour

#### Commands
When a user sends a message of any kind to the Telegram bot, Telegram contacts the configured webhook (`php artisan telegram:set-webhook`) to notify the application of the message.
The message is checked for a valid command - if there is one, the command is processed.
All Telegram commands are in [app/Telegram/Commands](/app/Telegram/Commands).
Telegram needs to be informed of these commands with `php artisan telegram:set-commands` so that it may present a convenient list to users of the bot.

During development, `php artisan telegram:poll` can be used instead of the webhook, which will start a long-term polling process to pull updates from Telegram rather than it pushing to the application.
This allows the Telegram bot to be tested without needing the application to be externally accessible to the internet.

## Glossary
- [Artisan](https://laravel.com/docs/11.x/artisan) - Laravel command line helper application (Used to run Sail)
- [Blade](https://laravel.com/docs/11.x/blade) - The built-in view templating engine in Laravel
- [Carbon](https://carbon.nesbot.com/docs/) - Fluent datetime library written in PHP (Laravel uses this by default for all datetimes)
- [Docker](https://www.docker.com/) - Container engine and other utilities for running containerized applications
- [Eloquent](https://laravel.com/docs/11.x/eloquent) - Laravel's built-in fluent ORM for interacting with the database
- [Laravel](https://laravel.com/) - Web application framework written in PHP
- [Sail](https://laravel.com/docs/11.x/sail) - Laravel system that manages a development container, proxying commands into the container
- [Sass/SCSS](https://sass-lang.com/) - Preprocessed extension language for CSS
- [Telegram](https://telegram.org/) - Instant messenger that Tracker provides a bot for interacting with
- [UUIDv7](https://www.ietf.org/archive/id/draft-peabody-dispatch-new-uuid-format-01.html#name-uuidv7-layout-and-bit-order) - Universally Unique Identifier, version 7
- [Vite](https://vitejs.dev/) - Asset bundling and cache-busting for the JS and image assets
