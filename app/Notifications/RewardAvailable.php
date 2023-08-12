<?php

namespace App\Notifications;

use App\Models\Reward;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RewardAvailable extends Notification implements ShouldQueue {
	use Queueable;

	/**
	 * Create a new notification instance.
	 */
	public function __construct(public Reward $reward) {
		// do nothing
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @return array<int, string>
	 */
	public function via(object $notifiable): array {
		return ['database'];
	}

	/**
	 * Get the array representation of the notification.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(object $notifiable): array {
		return [
			'reward_id' => $this->reward->id,
			'title' => "You've earned a reward!",
			'description' => "You're now eligible to claim the {$this->reward->hours}hr reward ({$this->reward->name}):\n{$this->reward->description}",
			'type' => 'success',
		];
	}
}
