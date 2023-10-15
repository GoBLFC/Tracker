<?php

namespace App\Providers;

use App\Models\Kiosk;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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
		// Set up custom Blade if directives
		Blade::if('devMode', fn () => Setting::isDevMode());
		Blade::if('lockdown', fn () => Setting::isLockedDown());
		Blade::if('kiosk', fn (bool $strict = false) => Kiosk::isSessionAuthorized($strict));
		Blade::if('activeEvent', fn () => Setting::activeEvent() !== null);
		Blade::if('admin', fn () => Auth::user()?->isAdmin() ?? false);
		Blade::if('manager', fn () => Auth::user()?->isManager() ?? false);
		Blade::if('lead', fn () => Auth::user()?->isLead() ?? false);
		Blade::if('gatekeeper', fn () => Auth::user()?->isGatekeeper() ?? false);
		Blade::if('banned', fn () => Auth::user()?->isBanned() ?? false);
	}
}
