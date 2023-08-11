<?php

namespace App\Telegram\Commands;

use App\Models\Event;
use App\Models\Setting;
use App\Models\User;
use Telegram\Bot\Commands\Command as BaseCommand;
use Telegram\Bot\Keyboard\Keyboard;

abstract class Command extends BaseCommand {
	/**
	 * Whether the command should be hidden from the help output
	 */
	public bool $hidden = false;

	/**
	 * Gets the user associated with the Telegram chat. If there isn't one, then reply with a message.
	 */
	protected function getChatUserOrReply(): ?User {
		$chatId = $this->getUpdate()->getChat()->id;
		$user = User::whereTgChatId($chatId)->first();
		if (!$user) {
			$this->replyWithMessage([
				'text' => "I don't have a BLFC volunteer account associated with you yet. Please link your account at the volunteer desk!",
			]);
		}
		return $user;
	}

	/**
	 * Gets the active event. If there isn't one, then reply with a message.
	 */
	protected function getActiveEventOrReply(): ?Event {
		$event = Setting::activeEvent();
		if (!$event) {
			$this->replyWithMessage([
				'text' => "Enthusiastic, are we? There isn't any ongoing event right now.",
				'reply_markup' => $this->buildStandardActionsKeyboard(),
			]);
		}
		return $event;
	}

	/**
	 * Builds reply markup for a keyboard that contains a list of standard actions while authenticated
	 */
	protected function buildStandardActionsKeyboard(): Keyboard {
		$keyboard = new Keyboard([
			'/code - Get quick sign-in code',
			'/hours - Show my hours clocked',
			'/rewards - Show available rewards',
		]);
		return $keyboard->setResizeKeyboard(true)->setIsPersistent(true)->setSelective(false);
	}
}
