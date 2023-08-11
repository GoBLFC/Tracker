<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Collection;
use Telegram\Bot\Laravel\Facades\Telegram;

class SetTelegramCommandsCommand extends Command implements Isolatable {
	/**
	 * The name and signature of the console command.
	 */
	protected $signature = 'telegram:set-commands';

	/**
	 * The console command description.
	 */
	protected $description = 'Sends the list of commands to Telegram';

	/**
	 * Execute the console command.
	 */
	public function handle(): void {
		$commands = new Collection(Telegram::getCommands());
		Telegram::setMyCommands([
			'commands' => $commands->filter(fn ($cmd) => !$cmd->hidden)
				->map(fn ($cmd) => [
					'command' => $cmd->getName(),
					'description' => $cmd->getDescription()
				])
				->values()
		]);
		$this->info('Command list sent to Telegram.');
	}
}
