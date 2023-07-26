<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Kiosk;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
	/**
	 * Register any application services.
	 */
	public function register(): void {
		//
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void {
		// Make some values available to all views
		View::share('devMode', Setting::isDevMode());
		View::share('activeEvent', Setting::activeEvent());
		View::share('isKiosk', Kiosk::isSessionAuthorized());
	}
}
