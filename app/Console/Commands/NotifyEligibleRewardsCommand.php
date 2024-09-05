<?php

namespace App\Console\Commands;

use App\Console\LogFriendlyOutput;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\RewardAvailable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NotifyEligibleRewardsCommand extends Command {
	use LogFriendlyOutput;

	/**
	 * The name and signature of the console command.
	 */
	protected $signature = 'tracker:notify-rewards';

	/**
	 * The console command description.
	 */
	protected $description = 'Sends notifications to users for rewards they qualify for';

	/**
	 * Execute the console command.
	 */
	public function handle(): void {
		// Ensure there is an active event
		$event = Setting::activeEvent();
		if (!$event) {
			$this->info('No active event to process time entries for.');
			return;
		}

		// Fetch all users that have time entries for the event and eager load all of the relationships we'll need
		$users = User::whereHas('timeEntries', function ($query) use ($event) {
			$query->forEvent($event);
		})->with([
			'notifications' => function ($query) {
				$query->whereType(RewardAvailable::class);
			},
			'timeEntries' => function ($query) use ($event) {
				$query->forEvent($event);
			},
			'timeEntries.department.timeBonuses' => function ($query) use ($event) {
				$query->forEvent($event);
			},
			'rewardClaims' => function ($query) use ($event) {
				$query->forEvent($event);
			},
		])->lazy();

		// Notify users for any rewards they are now eligible for
		$notified = 0;
		/** @var User $user */
		foreach ($users as $user) {
			$rewardInfo = $user->getRewardInfo($event, $user->timeEntries);
			foreach ($rewardInfo['eligible'] as $reward) {
				// Make sure the user hasn't already been notified for the reward or claimed it
				if ($user->hasBeenNotifiedForEligibleReward($reward, $user->notifications)) continue;
				if ($rewardInfo['claimed']->contains($reward)) continue;

				// Notify the user
				$user->notify(new RewardAvailable($reward));
				$this->info("User {$user->id} notified for {$reward->id}.");
				$notified++;
			}
		}

		$usersWord = Str::plural('user', $notified);
		$this->info("Notified {$notified} {$usersWord} of eligible rewards.");
	}
}
