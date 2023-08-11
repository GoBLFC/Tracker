<?php

namespace App\Telegram\Commands;

class UnlinkCommand extends Command {
	protected string $name = 'unlink';
	protected string $description = 'Unlink your volunteer account';

	public function handle(): void {
		// Make sure we have a user for the chat
		$user = $this->getChatUserOrReply();
		if (!$user) return;

		// Clear the user's chat ID
		$user->tg_chat_id = null;
		$user->save();

		$this->replyWithMessage([
			'text' => "Your volunteer account has been unlinked.\nTo continue using the bot, you will need to re-scan the QR code at the volunteer desk.",
		]);
	}
}
