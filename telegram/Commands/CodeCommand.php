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
use Longman\TelegramBot\Entities\ServerResponse;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class CodeCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'code';

    /**
     * @var string
     */
    protected $description = 'Get quick sign-in code';

    /**
     * @var string
     */
    protected $usage = '/code';

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
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();

        $currChatID = $message->getChat()->getId();

        $user = $db->getUserByTelegramID($currChatID);
        if ($user == null) {
            return Request::sendMessage([
                'chat_id' => $currChatID,
                'text' => "I don't have a BLFC volunteer account associated with you yet. Please link your account at the volunteer desk!",
            ]);
        }

		$db->setQuickCode($user['id'], rand(1001, 9998));

        return Request::sendMessage([
            'chat_id' => $currChatID,
            'text' => "***Quick Signin Code:*** $code" . PHP_EOL . "***Please use within 30 seconds***",
            'parse_mode' => 'Markdown'
        ]);
    }
}