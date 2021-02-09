<?php
// Load composer
use Longman\TelegramBot\Request;

define('TRACKER', TRUE);

require dirname( __DIR__ , 1 ) . '/vendor/autoload.php';
include(dirname( __DIR__ , 1 ) . '/includes/sql.php');
include(dirname( __DIR__ , 1 ) . '/includes/functions.php');

$bot_api_key = '821217613:AAFppYNuWAgLULFFKj93CQNh1eBVpIULSvY';
$bot_username = 'BLFC_BOT';

// Define all IDs of admin users in this array (leave as empty array if not used)
$admin_users = [
//    123,
    585471691, 274776646
];

// Define all paths for your custom commands in this array (leave as empty array if not used)
$commands_paths = [
    './Commands/',
];

// Enter your MySQL database credentials
//$mysql_credentials = [
//    'host'     => 'localhost',
//    'user'     => 'dbuser',
//    'password' => 'dbpass',
//    'database' => 'dbname',
//];

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Add commands paths containing your custom commands
    $telegram->addCommandsPaths($commands_paths);

    // Enable admin users
    $telegram->enableAdmins($admin_users);

    // Enable MySQL
    //$telegram->enableMySql($mysql_credentials);

    // Logging (Error, Debug and Raw Updates)
    //Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__ . "/{$bot_username}_error.log");
    //Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__ . "/{$bot_username}_debug.log");
    //Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__ . "/{$bot_username}_update.log");

    // If you are using a custom Monolog instance for logging, use this instead of the above
    //Longman\TelegramBot\TelegramLog::initialize($your_external_monolog_instance);

    // Set custom Upload and Download paths
    //$telegram->setDownloadPath(__DIR__ . '/Download');
    //$telegram->setUploadPath(__DIR__ . '/Upload');

    // Here you can set some command specific parameters
    // e.g. Google geocode/timezone api key for /date command
    //$telegram->setCommandConfig('date', ['google_api_key' => 'your_google_api_key_here']);

    // Requests Limiter (tries to prevent reaching Telegram API limits)
    //$telegram->enableLimiter();

    // Handle telegram webhook request
    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    //echo $e;
    // Log telegram errors
    Longman\TelegramBot\TelegramLog::error($e);
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
    // Silence is golden!
    // Uncomment this to catch log initialisation errors
    //echo $e;
}
