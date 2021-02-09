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
use Longman\TelegramBot\Entities\Keyboard;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class HoursCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'hours';

    /**
     * @var string
     */
    protected $description = 'Show hours clocked';

    /**
     * @var string
     */
    protected $usage = '/hours';

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
        //$text    = 'Hi there!' . PHP_EOL . 'Type /help to see all commands! ' . PHP_EOL . 'PARAM: ' . $message->getText(true) . ' ID: ' . $chat_id;

        $user = getUserByTGCID($currChatID);
        if ($user == null) {
            return Request::sendMessage([
                'chat_id' => $currChatID,
                'text' => "I don't have a BLFC volunteer account associated with you yet. Please link your account at the volunteer desk!",
            ]);
        }

        $minsToday = getMinutesToday($user['id']);
        $minsTotal = (calculateBonusTime($user['id'], false) + getMinutesTotal($user['id']));
        $timeToday = floor($minsToday / 60) . "h " . $minsToday % 60 . "m";
        $timeEarned = floor($minsTotal / 60) . "h " . $minsTotal % 60 . "m";

        return Request::sendMessage([
            'chat_id' => $currChatID,
            'text' => "***Time Today:*** $timeToday" . PHP_EOL . "***Total Time Earned:*** $timeEarned",
            'parse_mode' => 'Markdown'
        ]);
    }
}