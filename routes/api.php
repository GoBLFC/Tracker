<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

$botToken = config('telegram.bots.tracker.token');
Route::post("/telegram/{$botToken}/webhook", function () {
	Telegram::commandsHandler(true);
	return 'ok';
})->name('telegram.tracker.webhook');
