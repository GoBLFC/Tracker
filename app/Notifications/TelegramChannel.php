<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramChannel {
	public function send(object $notifiable, Notification $notification): void {
		$type = get_class($notifiable);

		// Skip the notification if the user doesn't have a linked Telegram chat
		if (!$notifiable->tg_chat_id) {
			Log::debug("Skipping Telegram notification {$notification->id} for {$type} {$notifiable->id}");
			return;
		}

		Log::debug("Sending Telegram notification {$notification->id} for {$type} {$notifiable->id}");

		$message = $notification->toTelegram($notifiable);
		Telegram::sendMessage(array_merge(['chat_id' => $notifiable->tg_chat_id], $message));
	}
}
