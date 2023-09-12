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
	Route::get('/disabled/account', 'getBanned')->name('auth.banned');
});

Route::middleware(['auth', 'not-banned', 'lockdown'])->group(function () {
	Route::controller(\App\Http\Controllers\TrackerController::class)->group(function () {
		Route::get('/', 'getIndex')->name('tracker.index');
		Route::get('/disabled/site', 'getLockdown')->name('tracker.lockdown')->withoutMiddleware('lockdown');
		Route::post('/time/checkin', 'postCheckIn')->name('tracker.checkin.post');
		Route::post('/time/checkout', 'postCheckOut')->name('tracker.checkout.post');
		Route::post('/time/{timeEntry}/checkout', 'postCheckOut')->name('tracker.time.checkout.post');
		Route::delete('/time/{timeEntry}', 'deleteTimeEntry')->name('tracker.time.delete');
		Route::get('/users/{user}/stats', 'getStats')->name('tracker.user.stats');
		Route::put('/users/{user}/time', 'putTimeEntry')->name('tracker.time.put');
		Route::get('/users/{user}/time/event/{event}', 'getStats')->name('tracker.user.stats.event');
	});

	Route::controller(\App\Http\Controllers\UserController::class)->group(function () {
		Route::get('/users/search', 'getSearch')->name('users.search');
		Route::post('/users', 'store')->name('users.store');
		Route::patch('/users/{user}', 'update')->name('users.update');
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

	Route::apiResource('settings', \App\Http\Controllers\SettingController::class)->only(['update', 'destroy']);
	Route::apiResource('departments', \App\Http\Controllers\DepartmentController::class);
	Route::apiResource('events', \App\Http\Controllers\EventController::class);
	Route::apiResource('events.bonuses', \App\Http\Controllers\TimeBonusController::class)->shallow();
	Route::apiResource('events.rewards', \App\Http\Controllers\RewardController::class)->shallow();

	Route::controller(\App\Http\Controllers\ManagementController::class)->group(function () {
		Route::middleware('role:lead')->group(function () {
			Route::get('/lead', 'getLeadIndex')->name('management.lead');
		});

		Route::middleware('role:manager')->group(function () {
			Route::get('/manage', 'getManageIndex')->name('management.manage');
		});

		Route::middleware('role:admin')->group(function () {
			Route::get('/admin/site', 'getAdminSiteSettings')->name('admin.site');
			Route::get('/admin/users', 'getAdminUserRoles')->name('admin.users');
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
