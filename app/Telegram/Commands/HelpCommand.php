<?php

namespace App\Telegram\Commands;

class HelpCommand extends Command {
	protected string $name = 'help';
	protected array $aliases = ['commands', 'listcommands', '?'];
	protected string $description = 'View the list of commands';

	public function handle(): void {
		$commands = array_filter($this->telegram->getCommands(), fn ($cmd) => !$cmd->hidden);

		$text = '';
		foreach ($commands as $name => $handler) {
			$text .= sprintf('/%s - %s' . PHP_EOL, $name, $handler->getDescription());
		}

		$this->replyWithMessage(['text' => $text]);
	}
}
