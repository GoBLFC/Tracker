<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/about', 'About')->name('about');

Route::controller(\App\Http\Controllers\AuthController::class)->group(function () {
	Route::get('/login', 'getLogin')->name('auth.login')->middleware('guest');
	Route::get('/logout', 'getLogout')->name('auth.logout');
	Route::get('/auth/redirect', 'getRedirect')->name('auth.redirect');
	Route::get('/auth/callback', 'getCallback')->name('auth.callback');
	Route::post('/auth/quickcode', 'postQuickcode')->name('auth.quickcode.post');
	Route::get('/disabled/account', 'getBanned')->name('auth.banned');
});

Route::middleware(['auth', 'not-banned', 'lockdown'])->group(function () {
	Route::controller(\App\Http\Controllers\VolunteerController::class)->group(function () {
		Route::get('/', 'index')->name('volunteer.index');
		Route::get('/disabled/site', 'lockdown')->name('volunteer.lockdown')->withoutMiddleware('lockdown');
		Route::post('/events/{event}/checkin', 'checkIn')->name('volunteer.time.checkin');
		Route::post('/time/{timeEntry}/checkout', 'checkOut')->name('volunteer.time.checkout');
		Route::put('/events/{event}/users/{user}/time', 'storeTimeEntry')->name('volunteer.time.store');
		Route::delete('/time/{timeEntry}', 'destroyTimeEntry')->name('volunteer.time.destroy');
	});

	Route::get('/users/search', [\App\Http\Controllers\UserController::class, 'getSearch'])->name('users.search');
	Route::apiResource('users', \App\Http\Controllers\UserController::class)->except(['show']);

	Route::controller(\App\Http\Controllers\NotificationController::class)->group(function () {
		Route::get('/alerts', 'getIndex')->name('notifications.index');
		Route::post('/alerts/acknowledge', 'postAcknowledge')->name('notifications.acknowledge');
	});

	Route::controller(\App\Http\Controllers\RewardClaimController::class)->group(function () {
		Route::get('/users/{user}/claims', 'getClaims')->name('users.claims');
		Route::get('/users/{user}/claims/event/{event}', 'getClaims')->name('users.claims.event');
		Route::put('/users/{user}/claims', 'store')->name('users.claims.store');
		Route::delete('/claims/{rewardClaim}', 'destroy')->name('claims.destroy');
	});

	Route::controller(\App\Http\Controllers\KioskController::class)->group(function () {
		Route::post('/kiosks/authorize', 'postAuthorize')->name('kiosks.authorize.post');
		Route::post('/kiosks/deauthorize', 'postDeauthorize')->name('kiosks.deauthorize.post');
	});

	Route::apiResource('settings', \App\Http\Controllers\SettingController::class)
		->only(['index', 'update', 'destroy'])
		->parameter('settings', 'setting:name');
	Route::apiResource('events', \App\Http\Controllers\EventController::class);
	Route::apiResource('events.departments', \App\Http\Controllers\DepartmentController::class)->shallow();
	Route::apiResource('events.bonuses', \App\Http\Controllers\TimeBonusController::class)->shallow();
	Route::apiResource('events.rewards', \App\Http\Controllers\RewardController::class)->shallow();
	Route::apiResource('events.attendee-logs', \App\Http\Controllers\AttendeeLogController::class)->shallow();

	Route::controller(\App\Http\Controllers\AttendeeLogController::class)->group(function () {
		Route::get('/attendee-logs', 'index')->name('attendee-logs.index');
		Route::put('/attendee-logs/{attendeeLog}/users', 'storeUser')->name('attendee-logs.users.store');
		Route::delete('/attendee-logs/{attendeeLog}/{type}/{user}', 'destroyUser')->name('attendee-logs.users.destroy');
	});

	Route::controller(\App\Http\Controllers\ReportController::class)->middleware('role:admin')->group(function () {
		Route::get('/reports', 'index')->name('reports.index');
		Route::get('/reports/{report}', 'show')->name('reports.show');
		Route::get('/reports/{report}/{fileType}', 'export')->name('reports.export');
		Route::get('/events/{event}/reports', 'index')->name('events.reports.index');
		Route::get('/events/{event}/reports/{report}', 'show')->name('events.reports.show');
		Route::get('/events/{event}/reports/{report}/{fileType}', 'export')->name('events.reports.export');
	});

	Route::controller(\App\Http\Controllers\ManagementController::class)->middleware('role:manager')->group(function () {
		Route::get('/manage/{event?}', 'getManageIndex')->name('management.manage');
		Route::get('/manage/{event}/volunteer/{user}', 'getManageIndex')->name('management.manage.volunteer');
	});
});
