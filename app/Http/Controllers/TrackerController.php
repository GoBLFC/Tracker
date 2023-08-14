<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CheckInRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackerController extends Controller {
	/**
	 * Render the tracker index
	 */
	public function getIndex(): View|RedirectResponse {
		/** @var User */
		$user = Auth::user();

		// If the user has notifications, present those first
		if ($user->unreadNotifications()->exists()) return redirect()->route('notifications.index');

		$stats = $user->getTimeStats();
		return view('tracker.index', [
			'user' => $user,
			'stats' => $stats,
			'ongoing' => $stats['entries']->first(fn (TimeEntry $entry) => $entry->isOngoing()),
			'departments' => $user->isAdmin() ? Department::all() : Department::whereHidden(false)->get(),
		]);
	}

	/**
	 * Check in (start a time entry) for a department
	 */
	public function postCheckIn(CheckInRequest $request): JsonResponse|RedirectResponse {
		/** @var User */
		$user = Auth::user();

		// Make sure there's an active event
		$event = Setting::activeEvent();
		if (!$event) {
			$error = 'There is no active event to check in to.';
			return $request->expectsJson()
				? response()->json(['error' => $error], 409)
				: redirect()->back()->withError($error)->withInput();
		}

		// Ensure there isn't already an ongoing time entry
		$ongoing = $user->timeEntries()->forEvent()->ongoing()->count();
		if ($ongoing > 0) {
			$error = 'Cannot check in with an already-ongoing time entry.';
			return $request->expectsJson()
				? response()->json(['error' => $error], 409)
				: redirect()->back()->withError($error)->withInput();
		}

		// Create a new time entry
		$entry = new TimeEntry([
			'user_id' => $user->id,
			'creator_user_id' => $user->id,
			'event_id' => $event->id,
			'department_id' => $request->input('department'),
			'start' => now(),
		]);
		$entry->save();

		return $request->expectsJson()
			? response()->json(['entry' => $entry])
			: redirect()->route('tracker.index');
	}

	/**
	 * Check out for the ongoing time entry
	 */
	public function postCheckOut(Request $request): JsonResponse|RedirectResponse {
		/** @var User */
		$user = Auth::user();

		// Make sure there's an active event
		$event = Setting::activeEvent();
		if (!$event) {
			$error = 'There is no active event to check in to.';
			return $request->expectsJson()
				? response()->json(['error' => $error], 409)
				: redirect()->back()->withError($error)->withInput();
		}

		// Get the ongoing time entry
		$ongoing = $user->timeEntries()->forEvent()->ongoing()->first();
		if (!$ongoing) {
			$error = 'There is no ongoing time entry to check out for.';
			return $request->expectsJson()
				? response()->json(['error' => $error], 409)
				: redirect()->back()->withError($error)->withInput();
		}

		// End the time entry
		$ongoing->stop = now();
		$ongoing->save();

		return $request->expectsJson()
			? response()->json(['entry' => $ongoing, 'stats' => $user->getTimeStats()])
			: redirect()->route('tracker.index');
	}
}
