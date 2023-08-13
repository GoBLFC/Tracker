<?php

namespace App\Notifications;

use App\Models\Reward;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RewardClaimed extends Notification implements ShouldQueue {
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
		return ['database', TelegramChannel::class];
	}

	/**
	 * Get the array representation of the notification.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(object $notifiable): array {
		$name = htmlspecialchars($this->reward->name);
		return [
			'reward_id' => $this->reward->id,
			'title' => "You've claimed a reward",
			'description' => "You claimed the <strong>{$this->reward->hours}hr</strong> reward ({$name}).\nIf this doesn't seem correct, please let a Volunteer Manager know ASAP.",
			'type' => 'info',
		];
	}

	/**
	 * Get the Telegram message representation of the notification
	 *
	 * @return array<string, mixed>
	 */
	public function toTelegram(object $notifiable): array {
		return [
			'text' => "ℹ️ You claimed the {$this->reward->hours}hr reward.\nIf this doesn't seem correct, please let a Volunteer Manager know ASAP.",
			'parse_mode' => 'HTML',
		];
	}
}
