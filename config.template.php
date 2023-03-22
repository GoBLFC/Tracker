<?php

// This is a template configuration file.
// Copy and rename this file to `config.php` and edit configuration values as needed.

// General configuration
$CANONICAL_URL = "https://tracker.youreventhere.org";

// Set the timezone for all date/time functions in Tracker
// List of PHP supported timezones: https://www.php.net/manual/en/timezones.php
// Use this if you can't change the `date.timezone` setting in php.ini
// Leaving this unset will use PHP's default timezone
// Leaving PHP's timezone also unset will default to UTC
$TIMEZONE = null;

// Database connection information
$DB_HOST = "localhost";
$DB_USERNAME = "username";
$DB_PASSWORD = "password";
$DB_NAME = "dbname";

// OAuth client information
$OAUTH_CLIENT_ID = "id";
$OAUTH_CLIENT_SECRET = "secret";
$OAUTH_CONCAT_BASE_URL = "https://reg.youreventhere.org"; // Please do not include a trailing slash.

// Telegram bot information
$BOT_API_KEY = "API_KEY_HERE";
$BOT_USERNAME = "Tracker_Bot";
$BOT_ADMINS = []; // List of admin user IDs for the bot.

?>
