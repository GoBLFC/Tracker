# BLFC Volunteer Shift Tracker System

Tracker is an at-con time clock system for volunteers at [BLFC](https://goblfc.org). Volunteers can clock in and clock out for their shifts to log their hours, ensuring they get the rewards they deserve. Staff can manage volunteers and run reports on volunteer hours.

It is a PHP application, and will run on a typical LAMP stack.

## Installation

### Requirements

- Web server, such as Apache or Nginx
- PHP 8.0 (minimum), with the following extensions: `PDO` and `pdo_mysql`
- MySQL or MariaDB server
- Your event's instance of [ConCat](https://concat.app) to allow volunteers and staff to authenticate with the system
  - Users log in to the application using ConCat with OAuth.
  - You will need Developer access to your event's ConCat instance to create the OAuth app. Specifically, you will need the `oauth:manage` permission. Alternatively, have someone else create an OAuth app in ConCat for you and have them provide you the client ID and secret.
  - The OAuth app will also require the `volunteer:read` application permission for OAuth Bearer tokens, which is used for generating the Volunteer Applications report inside Tracker.

### Procedure

Copy the contents of this repo into your web server's document root. Next, install required dependencies using `composer install`. Finally, initialize the database using the provided `schema.sql` file. If you have access to a command line, this is simple to do:

```
$ mysql -u username -p dbname < schema.sql
```

Alternatively, if you use a GUI-based database management tool like phpMyAdmin, you can import the file there as well.

## Configuration

Configuration values are read from `config.php`. There is a template configuration file available at `config.template.php`. Copy and rename this file, then edit the variables as needed.

- **General configuration**
  - Set `$CANONICAL_URL` to be the public-facing base URL of your Tracker instance.
- **MySQL configuration**
  - Change the variables to reflect your MySQL credentials and database name.
  - It is recommended to create a user exclusively for this web app that will have full permission over the Tracker database and this database only. No need to make a memorable password here: Make it long and random.
- **OAuth configuration**
  - Fill out the client ID and secret variables with the values you got from ConCat.
  - `$OAUTH_CONCAT_BASE_URL` is the FQDN of your event's ConCat instance.
- **Telegram bot configuration**
  - Volunteers can opt into receiving four digit sign in codes from a [Telegram bot](https://core.telegram.org/bots) to make subsequent logins faster. It also allows them to check their hours without logging into Tracker, and receive notifications for when they are given rewards.
  - You must create and provide the details for the Telegram bot yourself. Specifically, the bot's API key and username must be provided in the configuration file, as well as a list of Telegram user IDs who are to be considered admins.

## Development

### Environment

On Windows, installing [XAMPP](https://www.apachefriends.org/download.html) is a quick and easy way to get a full web server stack running on your computer that you can use for local development.

On Linux, you may wish to install PHP and MySQL through your distributions's package manager. Or, if you use Docker, you can use the provided `docker-compose.yml` file to spin up an Apache web server with PHP and a MySQL server with a pre-configured user and database. Please view this file for the credentials. **Do not use this Docker Compose file in a production environment.** It is intended for local development purposes only.

In ConCat, you may want to create an OAuth app specifically for local testing. To do this, create a new app and specify these as the callback URIs:

- `http://localhost:8080`
- `http://127.0.0.1:8080`

If you're developing on a different server internally or you use a different port number, you will need to change the addresses accordingly.

### Dependencies

This project uses [Composer](https://getcomposer.org/) to manage dependencies. See `composer.json` to see which ones are used, and install them using `composer install`.
