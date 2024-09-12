<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramChannel {
	public function send(object $notifiable, Notification $notification): void {
		$notifiableType = get_class($notifiable);
		$notificationType = get_class($notification);

		// Skip the notification if the user doesn't have a linked Telegram chat
		if (!$notifiable->tg_chat_id) {
			Log::info("Skipping Telegram notification {$notificationType} {$notification->id} for {$notifiableType} {$notifiable->id}");
			return;
		}

		Log::info("Sending Telegram notification {$notificationType} {$notification->id} for {$notifiableType} {$notifiable->id}");

		$message = $notification->toTelegram($notifiable);
		Telegram::sendMessage(array_merge(['chat_id' => $notifiable->tg_chat_id], $message));
	}
}
