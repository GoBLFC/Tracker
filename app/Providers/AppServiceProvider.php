<?php

namespace App\Providers;

use App\Models\Kiosk;
use App\Models\Setting;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
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
		// Make the active event available to all views
		try {
			View::share('activeEvent', Setting::activeEvent());
		} catch (\Throwable $err) {
			Log::error('Unable to share global view values', ['error' => $err]);
		}

		// Set up custom Blade if directives
		Blade::if('devmode', fn () => Setting::isDevMode());
		Blade::if('kiosk', fn (bool $strict = false) => Kiosk::isSessionAuthorized($strict));
	}
}
