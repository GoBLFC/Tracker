<?php

namespace App\Telegram\Commands;

use App\Models\QuickCode;

class QuickCodeCommand extends Command {
	protected string $name = 'code';
	protected string $description = 'Get quick sign-in code';

	public function handle(): void {
		// Make sure we have a user for the chat
		$user = $this->getChatUserOrReply();
		if (!$user) return;

		// Make a quick code and reply with it
		$quickCode = new QuickCode;
		$quickCode->user_id = $user->id;
		$quickCode->save();
		$this->replyWithMessage([
			'text' => "***Quick Sign In Code:*** {$quickCode->code}\nExpires in 30 seconds.",
			'parse_mode' => 'Markdown',
			'reply_markup' => $this->buildStandardActionsKeyboard(),
		]);
	}
}
