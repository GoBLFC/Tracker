<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\Setting;
use App\Models\TimeEntry;
use Illuminate\View\View;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CheckInRequest;
use App\Http\Requests\TimeEntryCreateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

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
		if ($user->timeEntries()->forEvent()->ongoing()->exists()) {
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
			'department_id' => $request->input('department_id'),
			'start' => now(),
		]);
		$entry->save();

		return $request->expectsJson()
			? response()->json(['time_entry' => $entry])
			: redirect()->route('tracker.index');
	}

	/**
	 * Check out for the ongoing time entry
	 */
	public function postCheckOut(Request $request, TimeEntry $timeEntry = null): JsonResponse|RedirectResponse {
		/** @var User */
		$user = Auth::user();

		if (!$timeEntry) {
			// Make sure there's an active event
			$event = Setting::activeEvent();
			if (!$event) {
				$error = 'There is no active event to check in to.';
				return $request->expectsJson()
					? response()->json(['error' => $error], 409)
					: redirect()->back()->withError($error)->withInput();
			}

			// Get the ongoing time entry
			$timeEntry = $user->timeEntries()->forEvent()->ongoing()->first();
			if (!$timeEntry) {
				$error = 'There is no ongoing time entry to check out for.';
				return $request->expectsJson()
					? response()->json(['error' => $error], 409)
					: redirect()->back()->withError($error)->withInput();
			}
		}

		// End the time entry if the user's permitted
		$this->authorize('update', $timeEntry);
		$timeEntry->stop = now();
		$timeEntry->save();

		return $request->expectsJson()
			? response()->json(['time_entry' => $timeEntry, 'stats' => $user->getTimeStats()])
			: redirect()->route('tracker.index');
	}

	/**
	 * Get time statistics for a user
	 */
	public function getStats(User $user, ?Event $event = null): JsonResponse {
		$this->authorize('viewAny', [TimeEntry::class, $user]);

		// Get the time stats and add a bonus_time property to each time entry
		$stats = $user->getTimeStats($event);
		foreach ($stats['entries'] as $entry) $entry->bonus_time = $entry->calculateBonusTime($event, $stats['bonuses']);

		return response()->json([
			'user' => $user,
			'stats' => $stats,
			'ongoing' => $stats['entries']->first(fn (TimeEntry $entry) => $entry->isOngoing()),
		]);
	}

	/**
	 * Create a time entry for a user
	 */
	public function putTimeEntry(TimeEntryCreateRequest $request, User $user): JsonResponse {
		// Make sure we have an event
		$input = $request->safe();
		if (!isset($input['event_id'])) $input['event_id'] = Setting::activeEvent()?->id;
		if (!$input['event_id']) return response()->json(['error' => 'No event to create time entry for.'], 409);

		// Don't allow an ongoing entry to be created if there already is one
		if (!isset($input['stop'])) {
			if ($user->timeEntries()->ongoing()->forEvent($input['event_id'])->exists()) {
				return response()->json(['error' => 'User already has an ongoing time entry.'], 409);
			}
		}

		// Create the time entry
		$entry = new TimeEntry;
		$entry->start = isset($input['start']) ? Carbon::parse($input['start'])->timezone(config('app.timezone')) : now();
		$entry->stop = isset($input['stop']) ? Carbon::parse($input['stop'])->timezone(config('app.timezone')) : null;
		$entry->notes = $input['notes'] ?? null;
		$entry->event_id = $input['event_id'];
		$entry->department_id = $input['department_id'];
		$entry->user_id = $user->id;
		$entry->creator_user_id = $request->user()->id;
		$entry->save();

		return response()->json(['time_entry' => $entry, 'raw' => Carbon::parse($input['start'])]);
	}

	/**
	 * Delete a time entry
	 */
	public function deleteTimeEntry(TimeEntry $timeEntry) {
		$this->authorize('delete', $timeEntry);
		$timeEntry->delete();
		return response()->json(null, 205);
	}

	/**
	 * Display the lockdown notice
	 */
	public function getLockdown(): View|RedirectResponse {
		if (!Setting::isLockedDown()) return redirect()->route('tracker.index');
		return view('tracker.lockdown');
	}
}
