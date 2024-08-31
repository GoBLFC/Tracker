<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

$botToken = config('telegram.bots.tracker.token');
Route::post("/telegram/{$botToken}/webhook", function () {
	Telegram::commandsHandler(true);
	return 'ok';
})->name('telegram.tracker.webhook');
