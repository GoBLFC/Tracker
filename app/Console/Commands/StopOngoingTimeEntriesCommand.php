<?php

namespace App\Console\Commands;

use App\Console\LogFriendlyOutput;
use App\Models\TimeEntry;
use App\Notifications\TimeEntryAutoStopped;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class StopOngoingTimeEntriesCommand extends Command {
	use LogFriendlyOutput;

	/**
	 * The name and signature of the console command.
	 */
	protected $signature = 'tracker:stop-ongoing';

	/**
	 * The console command description.
	 */
	protected $description = 'Stops any ongoing time entries';

	/**
	 * Execute the console command.
	 */
	public function handle(): void {
		// Ensure there are entries to stop
		$entries = TimeEntry::ongoing()->with('user')->get();
		if ($entries->count() === 0) {
			$this->info('No ongoing time entries to stop.');
			return;
		}

		$entryWord = Str::plural('entry', $entries->count());
		$this->info("Stopping {$entries->count()} ongoing time {$entryWord}...");

		// Set the stop time of each entry to a max of 1 hour after they started and notify the user
		$now = now();
		$hourAgo = now()->subHour();
		foreach ($entries as $entry) {
			$entry->stop = $entry->start->lt($hourAgo) ? $entry->start->avoidMutation()->addHour() : $now;
			$entry->auto = true;
			$entry->save();

			$entry->user->notify(new TimeEntryAutoStopped($entry));
			$this->info("TimeEntry {$entry->id} stopped at {$entry->stop}.");
		}

		$this->info("{$entries->count()} ongoing time {$entryWord} stopped.");
	}
}
