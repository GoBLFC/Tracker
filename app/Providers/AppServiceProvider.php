<?php

namespace App\Providers;

use App\Models\Kiosk;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
		// Define the API rate limits
		RateLimiter::for('api', function (Request $request) {
			return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
		});

		// Allow admins and disallow banned users through all gates, plus disallow non-managers if the site is locked down
		Gate::before(function (User $user): ?bool {
			if ($user->isBanned()) return false;
			if ($user->isAdmin()) return true;
			if (Setting::isLockedDown() && !$user->isManager()) return false;
			return null;
		});

		// Set up custom Blade if directives
		Blade::if('devMode', fn () => Setting::isDevMode());
		Blade::if('lockdown', fn () => Setting::isLockedDown());
		Blade::if('kiosk', fn (bool $strict = false) => Kiosk::isSessionAuthorized($strict));
		Blade::if('activeEvent', fn () => Setting::activeEvent() !== null);
		Blade::if('admin', fn () => Auth::user()?->isAdmin() ?? false);
		Blade::if('manager', fn () => Auth::user()?->isManager() ?? false);
		Blade::if('lead', fn () => Auth::user()?->isLead() ?? false);
		Blade::if('gatekeeper', fn () => Auth::user()?->isGatekeeper() ?? false);
		Blade::if('hasBackend', fn () => Auth::user()?->hasAnyBackendAccess() ?? false);
		Blade::if('banned', fn () => Auth::user()?->isBanned() ?? false);
	}
}
