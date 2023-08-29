<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
	/**
	 * Define the application's command schedule.
	 */
	protected function schedule(Schedule $schedule): void {
		$schedule->command('model:prune --no-ansi')
			->daily()
			->appendOutputTo(storage_path('logs/prune.log'));

		$schedule->command('tracker:stop-ongoing --loggable --no-ansi')
			->dailyAt(config('tracker.day_boundary_hour') . ':00')
			->appendOutputTo(storage_path('logs/stop-ongoing.log'));

		$schedule->command('tracker:notify-rewards --loggable --no-ansi')
			->everyFiveMinutes()
			->appendOutputTo(storage_path('logs/notify-rewards.log'));
	}

	/**
	 * Register the commands for the application.
	 */
	protected function commands(): void {
		$this->load(__DIR__ . '/Commands');

		require base_path('routes/console.php');
	}

	/**
	 * Get the timezone that should be used by default for scheduled events.
	 */
	protected function scheduleTimezone(): \DateTimeZone|string|null {
		return config('tracker.timezone');
	}
}
