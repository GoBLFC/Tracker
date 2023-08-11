<?php

namespace App\Telegram\Commands;

use App\Models\User;

class HelpCommand extends Command {
	protected string $name = 'help';
	protected array $aliases = ['commands', 'listcommands', 'showcommands', 'status', 'halp'];
	protected string $description = 'View status and list of commands';

	public function handle(): void {
		// Get the user status
		$chatId = $this->getUpdate()->getChat()->id;
		$user = User::whereTgChatId($chatId)->first();
		$displayName = $user ? htmlspecialchars($user->getDisplayName()) : null;
		$status = $user ? "<b>Volunteer account:</b>\n{$displayName}" : "You haven't linked a volunteer account yet.";

		// Build a list of commands to display
		$commands = array_filter($this->telegram->getCommands(), fn (Command $cmd) => $cmd->isVisible((bool) $user));
		$commandList = '';
		foreach ($commands as $name => $handler) {
			$commandList .= sprintf('/%s - %s' . PHP_EOL, $name, htmlspecialchars($handler->getDescription()));
		}

		$this->replyWithMessage([
			'text' => "{$status}\n\n<b>Commands:</b>\n{$commandList}",
			'parse_mode' => 'HTML',
			'reply_markup' => $user ? $this->buildStandardActionsKeyboard() : null,
		]);
	}
}
