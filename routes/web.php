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
	Route::get('/disabled', 'getBanned')->name('auth.banned');
});

Route::middleware(['auth', 'not-banned'])->group(function () {
	Route::controller(\App\Http\Controllers\TrackerController::class)->group(function () {
		Route::get('/', 'getIndex')->name('tracker.index');
		Route::post('/time/checkin', 'postCheckIn')->name('tracker.checkin.post');
		Route::post('/time/checkout', 'postCheckOut')->name('tracker.checkout.post');
		Route::post('/time/{timeEntry}/checkout', 'postCheckOut')->name('tracker.time.checkout.post');
		Route::get('/user/{user}/stats', 'getStats')->name('tracker.user.stats');
		Route::put('/user/{user}/time', 'putTimeEntry')->name('tracker.time.put');
		Route::get('/user/{user}/time/event/{event}', 'getStats')->name('tracker.user.stats.event');
		Route::delete('/time/{timeEntry}', 'deleteTimeEntry')->name('tracker.time.delete');
	});

	Route::controller(\App\Http\Controllers\UserController::class)->group(function () {
		Route::post('/user/create', 'postCreate')->name('user.create.post');
		Route::get('/user/search', 'getSearch')->name('user.search');
	});

	Route::controller(\App\Http\Controllers\NotificationsController::class)->group(function () {
		Route::get('/alerts', 'getIndex')->name('notifications.index');
		Route::post('/alerts/acknowledge', 'postAcknowledge')->name('notifications.acknowledge');
	});

	Route::controller(\App\Http\Controllers\RewardClaimController::class)->group(function () {
		Route::get('/user/{user}/claims', 'getClaims')->name('user.claims');
		Route::get('/user/{user}/claims/event/{event}', 'getClaims')->name('user.claims.event');
		Route::put('/user/{user}/claims', 'putClaim')->name('user.claims.put');
		Route::delete('/claim/{rewardClaim}', 'deleteClaim')->name('user.claims.delete');
	});

	Route::controller(\App\Http\Controllers\KioskController::class)->group(function () {
		Route::post('/kiosk/authorize', 'postAuthorize')->name('kiosk.authorize.post');
		Route::post('/kiosk/deauthorize', 'postDeauthorize')->name('kiosk.deauthorize.post');
	});

	Route::controller(\App\Http\Controllers\ManagementController::class)->group(function () {
		Route::middleware('role:lead')->group(function () {
			Route::get('/lead', 'getLeadIndex')->name('management.lead');
		});

		Route::middleware('role:manager')->group(function () {
			Route::get('/manager', 'getManagerIndex')->name('management.manage');
		});
	});
});
