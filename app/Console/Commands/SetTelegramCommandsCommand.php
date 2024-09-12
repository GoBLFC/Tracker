<?php

namespace App\Console\Commands;

use App\Telegram\Commands\Command as TelegramCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Telegram\Bot\Laravel\Facades\Telegram;

class SetTelegramCommandsCommand extends Command {
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
			'commands' => $commands->filter(fn (TelegramCommand $cmd) => !$cmd->hidden)
				->map(fn (TelegramCommand $cmd) => [
					'command' => $cmd->getName(),
					'description' => $cmd->getDescription(),
				])
				->values(),
		]);
		$this->info('Command list sent to Telegram.');
	}
}
