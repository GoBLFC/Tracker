<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NotificationController extends Controller {
	/**
	 * List all unread notifications
	 */
	public function getIndex(Request $request): InertiaResponse|JsonResponse {
		/** @var User */
		$user = Auth::user();

		// Build the list of unread notification content
		$notifications = $user->unreadNotifications
			->map(fn (DatabaseNotification $notif) => array_merge($notif->data, [
				'id' => $notif->id,
				'created_at' => $notif->created_at,
				'read_at' => $notif->read_at,
			]))
			->values();

		return $request->expectsJson()
			? response()->json(['notifications' => $notifications])
			: Inertia::render('Notifications', ['notifications' => $notifications]);
	}

	/**
	 * Acknowledge all unread notifications
	 */
	public function postAcknowledge(Request $request): JsonResponse|RedirectResponse {
		/** @var User */
		$user = Auth::user();
		$user->unreadNotifications()->update(['read_at' => now()]);
		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->route('tracker.index');
	}
}
