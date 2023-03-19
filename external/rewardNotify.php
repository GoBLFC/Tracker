<?php

//if (php_sapi_name() != 'cli') die('No.');

chdir(dirname(__FILE__));
include('../includes/functions.php');
include('sendTelegramMessage.php');

echo "Loading users...\n";
foreach ($db->listUsers() as $user) {
    // Get available rewards
    $rewards = getEligibleRewards($user['id']);

    // Act upon reward if notification has not already been sent
    echo "User: " . $user['first_name'] . "\n";
    foreach ($rewards as $reward) {
        if ($reward['claimed'] || !$reward['avail'] || $reward['hidden'] || $reward['hours'] == 0) continue;

        //Check if user has already been notified
        if ($db->hasBeenNotified($user['id'], $reward['id'])) {
            echo "Already Notified reward: " . $reward['name'] . "\n";
        } else {
            echo "New available reward: " . $reward['name'] . "\n";
            $db->createNotification($user['id'], "success", $reward['id'], "You can claim the following reward: <b>" . $reward['name'] . " - " . $reward['desc'] . "</b><br>Ask to claim this at the volunteer desk!");
            echo \Longman\TelegramBot\Commands\SystemCommands\sendTGMessage($user['id'], "You can claim the following reward: " . PHP_EOL . "***" . $reward['name'] . " - " . $reward['desc'] . "***" . PHP_EOL . PHP_EOL . "Ask to claim this at the volunteer desk!");
        }
    }
}