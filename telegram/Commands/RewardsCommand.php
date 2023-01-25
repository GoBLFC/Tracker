<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class RewardsCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'rewards';

    /**
     * @var string
     */
    protected $description = 'Show available rewards.';

    /**
     * @var string
     */
    protected $usage = '/rewards';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
    protected $private_only = false;

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();

        $currChatID = $message->getChat()->getId();

        $user = getUserByTGCID($currChatID);
        if ($user == null) {
            return Request::sendMessage([
                'chat_id' => $currChatID,
                'text' => "I don't have a BLFC volunteer account associated with you yet. Please link your account at the volunteer desk!",
            ]);
        }

        $rewardText = "✅ = Claimed | ⭐ = Available | ⏳ = Locked" . PHP_EOL . PHP_EOL;
        $rewards = getEligibleRewards($user['id']);
        if (sizeof($rewards) == 0) $rewardText = "No available rewards.";
        foreach ($rewards as $reward) {
			if ($reward['hidden']) continue;
            $state = ($reward['avail'] ? "⭐" : "⏳");
            if ($reward['claimed']) $state = "✅";

            $rewardText .= $state;
            $rewardText .= "***" . $reward['name'] . "*** - " . $reward['desc'] . PHP_EOL;
        }

        return Request::sendMessage([
            'chat_id' => $currChatID,
            'text' => $rewardText,
            'parse_mode' => 'Markdown'
        ]);
    }
}