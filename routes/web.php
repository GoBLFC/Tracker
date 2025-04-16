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
	Route::controller(\App\Http\Controllers\TrackerController::class)->group(function () {
		Route::get('/', 'getIndex')->name('tracker.index');
		Route::get('/disabled/site', 'getLockdown')->name('tracker.lockdown')->withoutMiddleware('lockdown');
		Route::post('/time/checkin', 'postCheckIn')->name('tracker.checkin.post');
		Route::post('/time/checkout', 'postCheckOut')->name('tracker.checkout.post');
		Route::post('/time/{timeEntry}/checkout', 'postCheckOut')->name('tracker.time.checkout.post');
		Route::delete('/time/{timeEntry}', 'destroyTimeEntry')->name('tracker.time.destroy');
		Route::get('/users/{user}/stats', 'getStats')->name('tracker.user.stats');
		Route::put('/users/{user}/time', 'storeTimeEntry')->name('tracker.time.store');
		Route::get('/users/{user}/time/event/{event}', 'getStats')->name('tracker.user.stats.event');
	});

	Route::controller(\App\Http\Controllers\UserController::class)->group(function () {
		Route::get('/users/search', 'getSearch')->name('users.search');
		Route::post('/users', 'store')->name('users.store');
		Route::patch('/users/{user}', 'update')->name('users.update');
		Route::delete('/users/{user}', 'destroy')->name('users.destroy');
	});

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
	Route::apiResource('departments', \App\Http\Controllers\DepartmentController::class);
	Route::apiResource('events', \App\Http\Controllers\EventController::class);
	Route::apiResource('events.bonuses', \App\Http\Controllers\TimeBonusController::class)->shallow();
	Route::apiResource('events.rewards', \App\Http\Controllers\RewardController::class)->shallow();
	Route::apiResource('events.attendee-logs', \App\Http\Controllers\AttendeeLogController::class)->shallow();
	Route::apiResource('users', \App\Http\Controllers\UserController::class)->only(['index', 'store', 'update']);

	Route::controller(\App\Http\Controllers\AttendeeLogController::class)->group(function () {
		Route::get('/attendee-logs', 'index')->name('attendee-logs.index');
		Route::put('/attendee-logs/{attendeeLog}/users', 'storeUser')->name('attendee-logs.users.store');
		Route::delete('/attendee-logs/{attendeeLog}/{type}/{user}', 'destroyUser')->name('attendee-logs.users.destroy');
	});

	Route::controller(\App\Http\Controllers\ManagementController::class)->group(function () {
		Route::middleware('role:manager')->group(function () {
			Route::get('/manage/{event?}', 'getManageIndex')->name('management.manage');
			Route::get('/manage/{event}/volunteer/{user}', 'getManageIndex')->name('management.manage.volunteer');
		});

		Route::middleware('role:admin')->group(function () {
			Route::get('/admin/departments', 'getAdminDepartments')->name('admin.departments');
			Route::get('/admin/events', 'getAdminEvents')->name('admin.events');
			Route::get('/admin/rewards', 'getAdminRewards')->name('admin.rewards');
			Route::get('/admin/event/{event}/rewards', 'getAdminRewards')->name('admin.event.rewards');
			Route::get('/admin/bonuses', 'getAdminBonuses')->name('admin.bonuses');
			Route::get('/admin/event/{event}/bonuses', 'getAdminBonuses')->name('admin.event.bonuses');
			Route::get('/admin/reports', 'getAdminReportList')->name('admin.reports');
			Route::get('/admin/event/{event}/reports', 'getAdminReportList')->name('admin.event.reports');
			Route::get('/admin/reports/{reportType}', 'getAdminReport')->name('admin.reports.view');
			Route::get('/admin/event/{event}/reports/{reportType}', 'getAdminReport')->name('admin.event.reports.view');
			Route::get('/admin/reports/{reportType}/{fileType}', 'getAdminReportExport')->name('admin.reports.export');
			Route::get('/admin/event/{event}/reports/{reportType}/{fileType}', 'getAdminReportExport')->name('admin.event.reports.export');
		});
	});
});
