<?php

namespace App\Providers;

use App\Models\Kiosk;
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
		Gate::define('admin', function (User $user): bool {
			return $user->isAdmin();
		});

		Gate::define('manager', function (User $user): bool {
			return $user->isManager();
		});

		Gate::define('lead', function (User $user): bool {
			return $user->isLead();
		});

		Gate::define('banned', function (User $user): bool {
			return $user->isBanned();
		});

		Gate::define('kiosk', function (): bool {
			return Kiosk::isSessionAuthorized();
		});

		Gate::before(function (User $user): ?bool {
			if ($user->isBanned()) return false;
			return null;
		});
	}
}
