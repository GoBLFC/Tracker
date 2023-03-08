<?php

require "../config.php";

// Load composer
require "../vendor/autoload.php";

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($BOT_API_KEY, $BOT_USERNAME);

    // Set webhook
    $result = $telegram->setWebhook("{$CANONICAL_URL}/telegram/hook.php");
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
    // echo $e->getMessage();
}