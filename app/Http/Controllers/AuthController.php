<?php

namespace App\Http\Controllers;

use App\Events\QuickCodeLogin;
use App\Http\Requests\QuickCodeRequest;
use App\Models\Kiosk;
use App\Models\QuickCode;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller {
	/**
	 * Presents the login page
	 */
	public function getLogin(): InertiaResponse {
		Inertia::clearHistory();
		return Inertia::render('Login');
	}

	/**
	 * Logs the user out and redirects to the login page
	 */
	public function getLogout(): Response {
		if (!Auth::check()) return redirect()->route('auth.login');
		Auth::logout();
		Inertia::clearHistory();

		$conCatLogout = $this->buildConCatLogoutUrl();
		if ($conCatLogout) return Inertia::location(redirect()->to($conCatLogout));

		return redirect()->route('auth.login');
	}

	/**
	 * Redirects to the beginning of the ConCat OAuth flow
	 */
	public function getRedirect(): Response {
		return Inertia::location(
			Socialite::driver('concat')
				->scopes(['pii:basic'])
				->redirect()
		);
	}

	/**
	 * Completes the ConCat OAuth flow, creates or updates the user's database entry, signs them in, and redirects to
	 * the intended destination.
	 */
	public function getCallback(): RedirectResponse {
		// Get the user details from OAuth and update the existing user in the DB or create a new one
		$oauthUser = Socialite::driver('concat')->user();
		$user = User::updateOrCreateFromOAuthUser($oauthUser);

		// Store the OAuth token in the session and log the user in
		session()->put('conCatToken', $oauthUser->token);
		Auth::login($user);

		// Promote the user to Volunteer if they're just an Attendee
		if ($user->role === Role::Attendee) {
			$user->role = Role::Volunteer;
			$user->save();
		}

		return redirect()->intended();
	}

	/**
	 * Logs the user in via a quick code
	 */
	public function postQuickcode(QuickCodeRequest $request): JsonResponse|RedirectResponse {
		// Prevent too many rapid failed attempts
		$rateLimitKey = "quickcode:{$request->ip()}";
		if (RateLimiter::tooManyAttempts($rateLimitKey, $perMinute = 5)) {
			$error = 'Too many failed quick code login attempts have been made from this location. Try again in a minute.';
			return $request->expectsJson()
				? response()->json(['error' => $error], 429)
				: redirect()->back()->withErrors(['code' => $error])->withInput();
		}

		// Retrieve the quick code
		$quickcode = QuickCode::with('user')
			->unexpired()
			->whereCode($request->code)
			->first();

		// Verify the quick code exists
		if (!$quickcode) {
			RateLimiter::hit($rateLimitKey);
			$error = 'Quick code not recognized.';
			return $request->expectsJson()
				? response()->json(['errors' => $error], 401)
				: redirect()->back()->withErrors(['code' => $error])->withInput();
		}

		// Log the user in and delete the quick code to ensure it can't be used again
		QuickCodeLogin::dispatch($quickcode);
		Auth::login($quickcode->user);
		$quickcode->delete();

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->route('tracker.index');
	}

	/**
	 * Display the banned notice
	 */
	public function getBanned(): InertiaResponse|RedirectResponse {
		if (!Auth::user()?->isBanned()) return redirect()->route('tracker.index');
		return Inertia::render('Suspended');
	}

	/**
	 * Builds a URL to redirect the user to for logging out of ConCat, if applicable.
	 * For this to be relevant:
	 * - The session must have a `conCatToken` (logged in via the ConCat OAuth flow)
	 * - The session must be for an authorized kiosk
	 * - The application must not be in dev mode
	 * The session's `conCatToken` is discarded as part of this process.
	 */
	protected function buildConCatLogoutUrl(): ?string {
		$token = session()->remove('conCatToken');
		if (!$token) return null;
		if (!Kiosk::isSessionAuthorized(true)) return null;
		if (Setting::isDevMode()) return null;

		$concatUri = config('services.concat.instance_uri');
		$concatId = config('services.concat.client_id');
		$return = urlencode(route('auth.login'));
		return "{$concatUri}/oauth/logout?client_id={$concatId}&access_token={$token}&next={$return}";
	}
}
