<?php

namespace App\Telegram\Commands;

use App\Models\TimeEntry;

class HoursCommand extends Command {
	protected string $name = 'hours';
	protected string $description = 'Show hours clocked';
	protected array $aliases = ['time', 'clock'];
	public ?bool $authVisibility = true;

	public function handle(): void {
		// Make sure we have a user for the chat
		$user = $this->getChatUserOrReply();
		if (!$user) return;

		// Ensure there's an active event
		if (!$this->getActiveEventOrReply()) return;

		// Calculate the time stats
		$stats = $user->getTimeStats();
		$timeToday = TimeEntry::humanDuration($stats['day']);
		$timeTotal = TimeEntry::humanDuration($stats['total']);

		// See if there's a running shift
		$ongoing = $user->timeEntries()->forEvent()->ongoing()->first();
		$shiftText = $ongoing ? "<b>You're currently clocked in!</b>\n\n<b>Shift time:</b> {$ongoing->getHumanDuration()}\n" : '';

		$this->replyWithMessage([
			'text' => "{$shiftText}<b>Time today:</b> {$timeToday}\n<b>Total time earned:</b> {$timeTotal}",
			'parse_mode' => 'HTML',
			'reply_markup' => $this->buildStandardActionsKeyboard(),
		]);
	}
}
