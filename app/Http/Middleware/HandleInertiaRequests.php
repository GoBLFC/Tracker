<?php

namespace App\Http\Middleware;

use App\Models\Kiosk;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware {
	/**
	 * The root template that's loaded on the first page visit.
	 *
	 * @see https://inertiajs.com/server-side-setup#root-template
	 *
	 * @var string
	 */
	protected $rootView = 'app';

	/**
	 * Determines the current asset version.
	 *
	 * @see https://inertiajs.com/asset-versioning
	 */
	public function version(Request $request): ?string {
		return parent::version($request);
	}

	/**
	 * Define the props that are shared by default.
	 *
	 * @see https://inertiajs.com/shared-data
	 *
	 * @return array<string, mixed>
	 */
	public function share(Request $request): array {
		// Changes here should also be made to the exception handler in /bootstrap/app.php
		return array_merge(parent::share($request), [
			'auth.user' => fn () => $request->user()?->only('id', 'badge_id', 'badge_name', 'username', 'role'),
			'activeEvent' => fn () => Setting::activeEvent()?->only('id', 'name'),
			'timezone' => fn () => config('tracker.timezone'),
			'kioskLifetime' => fn () => config('tracker.kiosk_lifetime'),
			'isGatekeeper' => fn () => $request->user()?->isGatekeeper() ?? false,
			'isDevMode' => fn () => Setting::isDevMode(),
			'isKiosk' => fn () => Kiosk::isSessionAuthorized(true),
			'isDebug' => fn () => config('app.debug'),
			'flash' => [
				'success' => fn () => $request->session()->get('success'),
				'error' => fn () => $request->session()->get('error'),
			],
		]);
	}
}
