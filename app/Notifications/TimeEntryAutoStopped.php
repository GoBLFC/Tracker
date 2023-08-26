<?php

namespace App\Notifications;

use App\Models\TimeEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TimeEntryAutoStopped extends Notification implements ShouldQueue {
	use Queueable;

	/**
	 * Create a new notification instance.
	 */
	public function __construct(public TimeEntry $timeEntry) {
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
		return [
			'time_entry_id' => $this->timeEntry->id,
			'title' => 'Automatically checked out',
			'description' => "You were automatically checked out from your shift last night because you may have forgotten to check out.\nYou've been credited with <strong>1 hour</strong> for the shift.\n<strong>Please verify your time with your department lead or the volunteer desk!</strong>",
			'type' => 'warning',
		];
	}

	/**
	 * Get the Telegram message representation of the notification
	 *
	 * @return array<string, mixed>
	 */
	public function toTelegram(object $notifiable): array {
		return [
			'text' => "⚠️ You were <u>automatically checked out</u> from your shift last night because you may have forgotten to check out.\nYou've been credited with <u>1 hour</u> for the shift.\n\n<b>Please verify your time with your department lead or the volunteer desk!</b>",
			'parse_mode' => 'HTML',
		];
	}
}
