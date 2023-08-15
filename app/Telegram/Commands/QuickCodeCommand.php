<?php

namespace App\Telegram\Commands;

use App\Models\QuickCode;

class QuickCodeCommand extends Command {
	protected string $name = 'code';
	protected string $description = 'Get quick sign-in code';
	protected array $aliases = ['quickcode', 'quick', 'signin', 'login'];
	public ?bool $authVisibility = true;

	public function handle(): void {
		// Make sure we have a user for the chat
		$user = $this->getChatUserOrReply();
		if (!$user) return;

		// Don't allow any fancy users to generate quick codes
		if ($user->isLead()) {
			$this->replyWithMessage([
				'text' => "In the interest of security, staff may not use quick sign-in codes. Sorry!",
				'reply_markup' => $this->buildStandardActionsKeyboard(),
			]);
			return;
		}

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
			'text' => "<b>Quick Sign-in Code:</b> <code>{$quickCode->code}</code>\nThis code expires in 30 seconds." . ($existingUnexpired ? ' Your old code was invalidated.' : ''),
			'parse_mode' => 'HTML',
			'reply_markup' => $this->buildStandardActionsKeyboard(),
		]);
	}
}
