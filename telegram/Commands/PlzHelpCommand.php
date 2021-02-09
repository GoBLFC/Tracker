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
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\Sticker;
use Longman\TelegramBot\Request;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class PlzHelpCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'plzhelp';

    /**
     * @var string
     */
    protected $description = 'PLZ HEWLP';

    /**
     * @var string
     */
    protected $usage = '/plzhelp';

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

        $keyboard = new Keyboard('/hours - Show my hours clocked', '/rewards - Show available rewards');

        $keyboard
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->setSelective(false);
        Request::sendSticker([
            'chat_id' => $currChatID,
            'sticker' => "CAACAgQAAxkBAAIwql5oyc6UCDU-9CXXba_tcSVHgCyFAAKDAQACh7pZBkRd1KmXSK3rGAQ",
        ]);

        return Request::sendSticker([
            'chat_id' => $currChatID,
            'sticker' => "CAACAgQAAxkBAAIwrF5oydGd__NtLqeUzJKHLS07m31pAAKKAQACh7pZBorYn1WQba9kGAQ",
            'reply_markup' => $keyboard,
        ]);
    }
}