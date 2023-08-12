<?php

namespace App\Console\Commands;

use App\Models\TimeEntry;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class StopOngoingTimeEntriesCommand extends Command {
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
		$entries = TimeEntry::ongoing()->get();
		if ($entries->count() === 0) {
			$this->info('No ongoing time entries to stop.');
			return;
		}

		$entryWord = Str::plural('entry', $entries->count());
		$this->info("Stopping {$entries->count()} ongoing time {$entryWord}...");

		// Set the stop time of each entry to a max of 1 hour after their start
		$now = now();
		$hourAgo = now()->subHour();
		foreach ($entries as $entry) {
			$entry->stop = $entry->start->lt($hourAgo) ? $entry->start->avoidMutation()->addHour() : $now;
			$this->info("TimeEntry {$entry->id} stopped at {$entry->stop}.");
			$entry->save();
		}

		$this->info("Ongoing time {$entryWord} stopped.");
	}
}
