<?php

namespace App\Telegram\Commands;

use App\Models\User;
use Telegram\Bot\Keyboard\Keyboard;

class LinkCommand extends Command {
	protected string $name = 'link';
	protected string $description = 'Link your volunteer account';
	protected array $aliases = ['start', 'connect'];
	protected string $pattern = '{setupKey: [a-zA-Z0-9]{32}$}';
	public ?bool $authVisibility = false;

	public function handle(): void {
		// Make sure this chat isn't already known
		$chatId = $this->getUpdate()->getChat()->id;
		$chatUser = User::whereTgChatId($chatId)->first();
		if ($chatUser) {
			$this->replyWithMessage([
				'text' => "Looks like I'm already familiar with you!\nUse /unlink to set me to a different volunteer account.",
				'reply_markup' => $this->buildStandardActionsKeyboard(),
			]);
			return;
		}

		// Ensure the setup key is provided
		$setupKey = $this->argument('setupKey');
		if (!$setupKey) {
			$this->replyWithmessage([
				'text' => "The setup key for your volunteer account wasn't provided.\nYou'll need to scan a QR code at the volunteer desk.",
			]);
			return;
		}

		// Verify the setup key is valid for a user
		$user = User::whereTgSetupKey($setupKey)->first();
		if (!$user) {
			$this->replyWithMessage([
				'text' => "Unable to validate your volunteer account.\nTry scanning a new QR code.",
			]);
			return;
		}

		// If the user already has an associated chat, inform them it's being moved
		if ($user->tg_chat_id) {
			$this->telegram->sendMessage([
				'chat_id' => $user->tg_chat_id,
				'text' => "I have been changed to report to another user.\nYou'll need to scan a new QR code to continue to get your volunteer time info from me.",
				'reply_markup' => Keyboard::remove(),
			]);
		}

		// Store the chat ID and regenerate the setup key (to prevent reuse)
		$user->tg_chat_id = $chatId;
		$user->generateTelegramSetupKey();
		$user->save();

		$this->replyWithMessage([
			'text' => "Thanks for volunteering, {$user->getDisplayName()}!\nUse /help or press these buttons to view more info.",
			'reply_markup' => $this->buildStandardActionsKeyboard(),
		]);
	}
}
