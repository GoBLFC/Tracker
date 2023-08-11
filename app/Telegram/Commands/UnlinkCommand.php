<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Keyboard\Keyboard;

class UnlinkCommand extends Command {
	protected string $name = 'unlink';
	protected string $description = 'Unlink your volunteer account';
	public ?bool $authVisibility = true;

	public function handle(): void {
		// Make sure we have a user for the chat
		$user = $this->getChatUserOrReply();
		if (!$user) return;

		// Clear the user's chat ID
		$user->tg_chat_id = null;
		$user->save();

		$this->replyWithMessage([
			'text' => "Your volunteer account has been unlinked.\nTo continue interacting with me, you will need to scan a new QR code at the volunteer desk.",
			'reply_markup' => Keyboard::remove(),
		]);
	}
}
