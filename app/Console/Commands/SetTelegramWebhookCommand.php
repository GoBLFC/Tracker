<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class SetTelegramWebhookCommand extends Command {
	/**
	 * The name and signature of the console command.
	 */
	protected $signature = 'telegram:set-webhook {--remove : Whether to remove the webhook}';

	/**
	 * The console command description.
	 */
	protected $description = 'Sends the webhook information to Telegram';

	/**
	 * Execute the console command.
	 */
	public function handle(): void {
		if (!$this->option('remove')) {
			Telegram::setWebhook([
				'url' => route('telegram.tracker.webhook'),
				'certificate_path' => config('telegram.bots.tracker.certificate_path'),
			]);
			$this->info('Webhook sent to Telegram.');
		} else {
			Telegram::removeWebhook();
			$this->info('Webhook removal sent to Telegram.');
		}
	}
}
