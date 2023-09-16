<?php

namespace App\Telegram\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Keyboard\Keyboard;
use Spatie\Activitylog\Facades\CauserResolver;
use Telegram\Bot\Exceptions\TelegramSDKException;

class LinkCommand extends Command {
	protected string $name = 'link';
	protected string $description = 'Link your volunteer account';
	protected array $aliases = ['start', 'connect'];
	protected string $pattern = '{setupKey: [a-zA-Z0-9]{32}$}';
	public ?bool $authVisibility = false;

	public function handle(): void {
		$trackerLink = static::trackerLink('Tracker site');

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
				'text' => "The setup key for your volunteer account wasn't provided.\nYou'll need to scan a QR code at the volunteer desk or the {$trackerLink}.",
				'parse_mode' => 'HTML',
			]);
			return;
		}

		// Verify the setup key is valid for a user
		$user = User::whereTgSetupKey($setupKey)->first();
		if (!$user) {
			$this->replyWithMessage([
				'text' => "Unable to validate your volunteer account.\nTry scanning a new QR code at the volunteer desk or the {$trackerLink}.",
				'parse_mode' => 'HTML',
			]);
			return;
		}

		// If the user already has an associated chat, inform them it's being moved
		// We catch and log any errors here so that if the old chat no longer exists, the new link still goes through
		if ($user->tg_chat_id) {
			try {
				$this->telegram->sendMessage([
					'chat_id' => $user->tg_chat_id,
					'text' => "I have been changed to report to another user.\nYou'll need to scan a new QR code (at the volunteer desk or the {$trackerLink}) to continue to get your volunteer time info from me.",
					'parse_mode' => 'HTML',
					'reply_markup' => Keyboard::remove(),
				]);
			} catch (TelegramSDKException $err) {
				Log::warning('Error notifying old Telegram chat for linkage change', [
					'oldChat' => $user->tg_chat_id,
					'newChat' => $chatId,
					'user' => $user->id,
					'error' => $err,
				]);
			}
		}

		// Store the chat ID and regenerate the setup key (to prevent reuse)
		CauserResolver::setCauser($user);
		$user->tg_chat_id = $chatId;
		$user->generateTelegramSetupKey()->save();

		$displayName = htmlspecialchars($user->display_name);
		$this->replyWithMessage([
			'text' => "Thanks for volunteering, <b>{$displayName}</b>!\nUse /help or press these buttons to view more info.",
			'parse_mode' => 'HTML',
			'reply_markup' => $this->buildStandardActionsKeyboard(),
		]);
	}
}
