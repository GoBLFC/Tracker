<?php

namespace App\Telegram\Commands;

use App\Models\User;

class StartCommand extends Command {
	protected string $name = 'start';
	protected string $description = 'Connect your volunteer account and begin interacting';
	protected string $pattern = '{setupKey: [a-zA-Z0-9]{32}$}';

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
				'text' => "Please provide the setup key for your volunteer account.\nPerhaps try re-scanning the QR code you were provided?",
			]);
			return;
		}

		// Verify the setup key is valid for a user
		$user = User::whereTgSetupKey($setupKey)->first();
		if (!$user) {
			$this->replyWithMessage([
				'text' => "Unable to validate volunteer account.\nPerhaps try re-scanning the QR code you were provided?",
			]);
			return;
		}

		// If the user already has an associated chat, inform them it's being moved
		if ($user->tg_chat_id) {
			$this->telegram->sendMessage([
				'chat_id' => $user->tg_chat_id,
				'text' => "I have been changed to report to another user.\nYou'll have to scan the QR code again to get your volunteer time info from me.",
			]);
		}

		// Store the chat ID and reply with confirmation
		$user->tg_chat_id = $chatId;
		$user->save();
		$this->replyWithMessage([
			'text' => "Thanks for volunteering!\nPress these buttons to view more info.",
			'reply_markup' => $this->buildStandardActionsKeyboard(),
		]);
	}
}
