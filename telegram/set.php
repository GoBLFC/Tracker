<?php

// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key = '821217613:AAFppYNuWAgLULFFKj93CQNh1eBVpIULSvY';
$bot_username = 'BLFC_BOT';
$hook_url = 'https://tracker.goblfc.org/telegram/hook.php';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Set webhook
    $result = $telegram->setWebhook($hook_url);
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
    // echo $e->getMessage();
}