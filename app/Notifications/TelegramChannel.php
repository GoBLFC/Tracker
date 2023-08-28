<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramChannel {
	public function send(object $notifiable, Notification $notification): void {
		if (!$notifiable->tg_chat_id) return;
		$message = $notification->toTelegram($notifiable);
		Telegram::sendMessage(array_merge(['chat_id' => $notifiable->tg_chat_id], $message));
	}
}
