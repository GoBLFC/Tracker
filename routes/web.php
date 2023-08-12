<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::controller(\App\Http\Controllers\AuthController::class)->group(function () {
	Route::get('/login', 'getLogin')->name('auth.login')->middleware('guest');
	Route::get('/logout', 'getLogout')->name('auth.logout');
	Route::get('/auth/redirect', 'getRedirect')->name('auth.redirect');
	Route::get('/auth/callback', 'getCallback')->name('auth.callback');
	Route::post('/auth/quickcode', 'postQuickcode')->name('auth.quickcode.post');
});

Route::controller(\App\Http\Controllers\TrackerController::class)->middleware('auth')->group(function () {
	Route::get('/', 'getIndex')->name('tracker.index');
	Route::post('/checkin', 'postCheckIn')->name('tracker.checkin.post');
	Route::post('/checkout', 'postCheckOut')->name('tracker.checkout.post');
});

Route::controller(\App\Http\Controllers\NotificationsController::class)->middleware('auth')->group(function () {
	Route::get('/alerts', 'getIndex')->name('notifications.index');
	Route::post('/alerts/acknowledge', 'postAcknowledge')->name('notifications.acknowledge');
});
