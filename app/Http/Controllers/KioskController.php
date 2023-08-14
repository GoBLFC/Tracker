<?php

namespace App\Http\Controllers;

use App\Models\Kiosk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KioskController extends Controller {
	/**
	 * Authorize the current session as a kiosk
	 */
	public function postAuthorize(Request $request): JsonResponse|RedirectResponse {
		$this->authorize('create', Kiosk::class);

		// Make sure the session isn't already authorized
		if (Kiosk::isSessionAuthorized(true)) {
			return $request->expectsJson()
				? response()->json(['error' => 'Session is already authorized as a kiosk.'])
				: redirect()->back()->withError('Session is already authorized as a kiosk.');
		}

		$kiosk = Kiosk::authorizeSession();

		return $request->expectsJson()
			? response()->json($kiosk)
			: redirect()->back()->withSuccess('Authorized this session as a kiosk.');
	}

	/**
	 * Deauthorize the current session as a kiosk
	 */
	public function postDeauthorize(Request $request): JsonResponse|RedirectResponse {
		$kiosk = Kiosk::findFromSession();
		$this->authorize('delete', $kiosk);
		$kiosk->deauthorize();
		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess('Deauthorized this session as a kiosk.');
	}
}
