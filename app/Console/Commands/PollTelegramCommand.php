<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Telegram\Bot\Laravel\Facades\Telegram;

class PollTelegramCommand extends Command implements Isolatable {
	/**
	 * The name and signature of the console command.
	 */
	protected $signature = 'telegram:poll';

	/**
	 * The console command description.
	 */
	protected $description = 'Polls Telegram for updates and handles received commands (primarily for development use)';

	/**
	 * Whether the command should continue running
	 */
	protected bool $shouldKeepRunning = true;

	/**
	 * Execute the console command.
	 */
	public function handle(): void {
		$this->trap([SIGTERM, SIGQUIT, SIGINT], fn () => $this->shouldKeepRunning = false);
		$this->info('Polling Telegram for updates...');
		while ($this->shouldKeepRunning) Telegram::commandsHandler();
		$this->info('Polling stopped.');
	}
}
