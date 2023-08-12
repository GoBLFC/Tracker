<?php

namespace App\Telegram\Commands;

use App\Models\Reward;

class RewardsCommand extends Command {
	protected string $name = 'rewards';
	protected string $description = 'Show available rewards';
	public ?bool $authVisibility = true;

	public function handle(): void {
		// Make sure we have a user for the chat
		$user = $this->getChatUserOrReply();
		if (!$user) return;

		// Ensure there's an active event
		$event = $this->getActiveEventOrReply();
		if (!$event) return;

		// Ensure there are some rewards for the event
		if ($event->rewards()->count() < 1) {
			$this->replyWithMessage([
				'text' => "There are no rewards available for {$event->name}.",
			]);
			return;
		}

		// Build the list of rewards
		$rewardInfo = $user->getRewardInfo();
		$rewardList = $rewardInfo['rewards']->sortBy('hours')
			->map(function (Reward $reward) use ($rewardInfo): string {
				$claimed = $rewardInfo['claimed']->contains($reward);
				$eligible = !$claimed && $rewardInfo['eligible']->contains($reward);
				$name = htmlspecialchars($reward->name);
				$description = htmlspecialchars($reward->description);
				return ($claimed ? '✅' : ($eligible ? '⭐' : '⏳')) . " <u><b>{$reward->hours}hr:</b> {$name}</u>\n{$description}";
			})
			->join("\n\n");

		$eventName = htmlspecialchars($event->name);
		$this->replyWithMessage([
			'text' => "<b><u>{$eventName} Rewards</u></b>\n✅ = Claimed | ⭐ = Available | ⏳ = Locked\n\n{$rewardList}",
			'parse_mode' => 'HTML',
		]);
	}
}
