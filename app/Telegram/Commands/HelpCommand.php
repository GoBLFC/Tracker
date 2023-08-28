<?php

namespace App\Telegram\Commands;

use App\Models\Setting;
use App\Models\User;

class HelpCommand extends Command {
	protected string $name = 'help';
	protected array $aliases = ['commands', 'listcommands', 'showcommands', 'status', 'halp'];
	protected string $description = 'View status and available commands';

	public function handle(): void {
		// Get the event status
		$event = Setting::activeEvent();
		$eventName = $event ? htmlspecialchars($event->name) : 'None 😔';
		$status = "<b>Ongoing event:</b>\n{$eventName}\n\n";

		// Get the user status
		$chatId = $this->getUpdate()->getChat()->id;
		$user = User::whereTgChatId($chatId)->first();
		$displayName = $user ? htmlspecialchars($user->getDisplayName()) : null;
		$status .= "<b>Volunteer account:</b>\n" . ($user ? "{$displayName} (#{$user->badge_id})" : "You haven't linked a volunteer account to me.") . "\n\n";

		// Build a list of commands to display
		$commands = array_filter($this->telegram->getCommands(), fn (Command $cmd) => $cmd->isVisible((bool) $user));
		$commandList = '';
		foreach ($commands as $name => $handler) {
			$commandList .= sprintf('/%s - %s' . PHP_EOL, $name, htmlspecialchars($handler->getDescription()));
		}

		$this->replyWithMessage([
			'text' => "{$status}<b>Commands:</b>\n{$commandList}",
			'parse_mode' => 'HTML',
			'reply_markup' => $user ? $this->buildStandardActionsKeyboard() : null,
		]);
	}
}
