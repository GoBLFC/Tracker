<?php

namespace App\Providers;

use App\Models\Kiosk;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider {
	/**
	 * The model to policy mappings for the application.
	 *
	 * @var array<class-string, class-string>
	 */
	protected $policies = [
		//
	];

	/**
	 * Register any authentication / authorization services.
	 */
	public function boot(): void {
		// Allow admins and disallow banned users through all gates, plus disallow non-managers if the site is locked down
		Gate::before(function (User $user): ?bool {
			if ($user->isBanned()) return false;
			if ($user->isAdmin()) return true;
			if (Setting::isLockedDown() && !$user->isManager()) return false;
			return null;
		});
	}
}
