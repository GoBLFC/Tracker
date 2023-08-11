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

		// Find an existing quick code for the user or make a new one
		$quickCode = $user->quickCodes()->first();
		$existingUnexpired = $quickCode && !$quickCode->isExpired();
		if (!$quickCode) {
			$quickCode = new QuickCode;
			$quickCode->user_id = $user->id;
		}

		// (re)Generate the code and save
		$quickCode->generateCode();
		$quickCode->save();

		$this->replyWithMessage([
			'text' => "***Quick Sign In Code:*** {$quickCode->code}\nExpires in 30 seconds." . ($existingUnexpired ? ' Old code invalidated.' : ''),
			'parse_mode' => 'Markdown',
			'reply_markup' => $this->buildStandardActionsKeyboard(),
		]);
	}
}
