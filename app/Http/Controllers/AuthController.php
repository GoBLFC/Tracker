<?php

namespace App\Http\Controllers;

use App\Events\QuickCodeLogin;
use App\Http\Requests\QuickCodeRequest;
use App\Models\QuickCode;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller {
	/**
	 * Presents the login page
	 */
	public function getLogin(): View {
		return view('auth.login');
	}

	/**
	 * Logs the user out and redirects to the login page
	 */
	public function getLogout(): RedirectResponse {
		if (!Auth::check()) return redirect()->route('auth.login');
		Auth::logout();

		// Redirect the user to log out of ConCat if applicable
		$token = session('concatToken');
		if ($token && !Setting::isDevMode()) {
			session()->remove('concatToken');
			$concatUri = config('services.concat.instance_uri');
			$concatId = config('services.concat.client_id');
			$return = urlencode(route('auth.login'));
			return redirect()->to("{$concatUri}/oauth/logout?client_id={$concatId}&access_token={$token}&next=$return");
		}

		return redirect()->route('auth.login');
	}

	/**
	 * Redirects to the beginning of the ConCat OAuth flow
	 */
	public function getRedirect(): RedirectResponse {
		return Socialite::driver('concat')
			->scopes(['pii:basic'])
			->redirect();
	}

	/**
	 * Completes the ConCat OAuth flow, creates or updates the user's database entry, signs them in, and redirects to
	 * the intended destination.
	 */
	public function getCallback(): RedirectResponse {
		$oauthUser = Socialite::driver('concat')->user();
		$user = User::updateOrCreateFromOAuthUser($oauthUser);
		session()->put('concatToken', $oauthUser->token);
		Auth::login($user);
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
				: redirect()->back()->withError($error)->withInput();
		}

		// Retrieve the quick code
		$quickcode = QuickCode::with('user')
			->unexpired()
			->whereCode($request->validated('code'))
			->first();

		// Verify the quick code exists
		if (!$quickcode) {
			RateLimiter::hit($rateLimitKey);
			$error = 'Quick code not recognized.';
			return $request->expectsJson()
				? response()->json(['error' => 'Quick code not recognized.'], 401)
				: redirect()->back()->withError($error)->withInput();
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
	public function getBanned(): View|RedirectResponse {
		if (!Auth::user()?->isBanned()) return redirect()->route('tracker.index');
		return view('auth.banned');
	}
}
