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
use Longman\TelegramBot\Entities\ServerResponse;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class StartCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Start command';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();

        $currChatID = $message->getChat()->getId();
        //$text    = 'Hi there!' . PHP_EOL . 'Type /help to see all commands! ' . PHP_EOL . 'PARAM: ' . $message->getText(true) . ' ID: ' . $chat_id;

        // Validate UID
        $tguid = $message->getText(true);

        $keyboard = new Keyboard('/code - Get quick sign-in code', '/hours - Show my hours clocked', '/rewards - Show available rewards');

        $keyboard
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->setSelective(false);

        if (getUserByTGCID($currChatID) != null) {
            return Request::sendMessage([
                'chat_id' => $currChatID,
                'text' => "Looks like I'm already familiar with you! Use /unlink to set me to a different volunteer account.",
                'reply_markup' => $keyboard,
            ]);
        }

        $user = getUserByTGUID($tguid);
        if ($user == null) {
            return Request::sendMessage([
                'chat_id' => $currChatID,
                'text' => "Unable to validate volunteer account. " . PHP_EOL . "(Code already scanned? Refresh for new QR code.)",
            ]);
        }

        if ($user['tg_chatid'] == "") {
            // New add
            $db->updateTGChat($currChatID, $tguid);
            return Request::sendMessage([
                'chat_id' => $currChatID,
                'text' => "Thanks for volunteering!" . PHP_EOL . "Press these buttons to view more info.",
                'reply_markup' => $keyboard,
            ]);
        } else {
            if ($user['tg_chatid'] == $currChatID) {
                // Already scanned
                return Request::sendMessage([
                    'chat_id' => $currChatID,
                    'text' => "Looks like I'm already familiar with you! Type /help for a list of what I can do.",
                    'reply_markup' => $keyboard,
                ]);
            } else {
                // Inform other acct
                Request::sendMessage([
                    'chat_id' => $user['tg_chatid'],
                    'text' => "I have been changed to report to another user. " . PHP_EOL . "You'll have to scan the QR code again to get your volunteer time info from me.",
                ]);

                $db->updateTGChat($currChatID, $tguid);

                // Update new acct
                return Request::sendMessage([
                    'chat_id' => $currChatID,
                    'text' => "Thanks for volunteering!" . PHP_EOL . "Press these buttons to view more info.",
                    'reply_markup' => $keyboard,
                ]);
            }

        }
    }
}