<?php

require "../config.php";

namespace Longman\TelegramBot\Commands\SystemCommands;

//if (php_sapi_name() != 'cli') die('No.');
define('TRACKER', TRUE);

chdir(dirname(__FILE__));

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

require_once('../includes/sql.php');
require_once('../includes/functions.php');
require_once('../vendor/autoload.php');

function sendTGMessage($userID, $message)
{
    global $BOT_API_KEY;
    global $BOT_USERNAME;

    $user = getUserByID($userID, true)[0];
    if ($user['tg_chatid'] == "") return "User does not have a chat ID!";

    try {
        // Create Telegram API object
        $telegram = new Telegram($BOT_API_KEY, $BOT_USERNAME);

        return Request::sendMessage([
            'chat_id' => $user['tg_chatid'],
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    } catch (Longman\TelegramBot\Exception\TelegramException $e) {
        // log telegram errors
        // echo $e->getMessage();
    }
}