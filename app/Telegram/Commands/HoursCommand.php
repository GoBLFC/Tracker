<?php

namespace App\Telegram\Commands;

use App\Models\TimeEntry;

class HoursCommand extends Command {
	protected string $name = 'hours';
	protected string $description = 'Show hours clocked';

	public function handle(): void {
		// Make sure we have a user for the chat
		$user = $this->getChatUserOrReply();
		if (!$user) return;

		// Ensure there's an active event
		if (!$this->getActiveEventOrReply()) return;

		// Calculate the time stats and provide them in a reply
		$stats = $user->getTimeStats();
		$timeToday = TimeEntry::humanDuration($stats['day']);
		$timeTotal = TimeEntry::humanDuration($stats['total']);
		$this->replyWithMessage([
			'text' => "***Time Today:*** {$timeToday}\n***Total Time Earned:*** {$timeTotal}",
			'parse_mode' => 'Markdown',
			'reply_markup' => $this->buildStandardActionsKeyboard(),
		]);
	}
}
