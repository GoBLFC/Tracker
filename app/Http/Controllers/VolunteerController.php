<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInRequest;
use App\Http\Requests\TimeEntryStoreRequest;
use App\Models\Event;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class VolunteerController extends Controller {
	/**
	 * Render the volunteer home
	 */
	public function index(): InertiaResponse|RedirectResponse {
		/** @var User */
		$user = Auth::user();

		// If the user has notifications, present those first
		if ($user->unreadNotifications()->exists()) return redirect()->route('notifications.index');

		return Inertia::render('VolunteerHome', [
			'volunteer' => fn () => $user->getVolunteerInfo(),
			'departments' => fn () => $user->isManager()
				? Setting::activeEvent()?->departments()?->orderBy('name')?->get()
				: Setting::activeEvent()?->departments()?->orderBy('name')?->whereHidden(false)?->get(),
			'rewards' => fn () => Setting::activeEvent()?->rewards,
			'telegramSetupUrl' => fn () => $user->getTelegramSetupUrl(),
			'hasTelegram' => fn () => !empty($user->tg_chat_id),
		]);
	}

	/**
	 * Check in (start a time entry) for a department
	 */
	public function checkIn(CheckInRequest $request, Event $event): JsonResponse|RedirectResponse {
		/** @var User */
		$user = Auth::user();

		// Ensure there isn't already an ongoing time entry
		if ($user->timeEntries()->forEvent($event)->ongoing()->exists()) {
			$error = 'Cannot check in with an already-ongoing time entry.';
			return $request->expectsJson()
				? response()->json(['error' => $error], 409)
				: redirect()->back()->withError($error);
		}

		// Create a new time entry
		$entry = new TimeEntry($request->validated());
		$entry->start = now();
		$entry->user_id = $user->id;
		$entry->creator_user_id = $user->id;
		$entry->event_id = $event->id;
		$entry->save();

		return $request->expectsJson()
			? response()->json(['time_entry' => $entry])
			: redirect()->route('volunteer.index');
	}

	/**
	 * Check out an ongoing time entry
	 */
	public function checkOut(Request $request, TimeEntry $timeEntry): JsonResponse|RedirectResponse {
		$this->authorize('update', $timeEntry);

		// Ensure the time entry is actually ongoing
		if (!$timeEntry->isOngoing()) {
			$error = 'The time entry has already been checked out.';
			return $request->expectsJson()
				? response()->json(['error' => $error], 409)
				: redirect()->back()->withError($error);
		}

		// End the time entry
		$timeEntry->stop = now();
		$timeEntry->save();

		return $request->expectsJson()
			? response()->json(['time_entry' => $timeEntry, 'stats' => $timeEntry->user->getTimeStats()])
			: redirect()->route('volunteer.index');
	}

	/**
	 * Create a time entry for a user
	 */
	public function storeTimeEntry(TimeEntryStoreRequest $request, Event $event, User $user): JsonResponse {
		// Don't allow an ongoing entry to be created if there already is one
		$input = $request->safe();
		if (!isset($input['stop'])) {
			if ($user->timeEntries()->ongoing()->forEvent($event)->exists()) {
				return response()->json(['error' => 'User already has an ongoing time entry.'], 409);
			}
		}

		// Create the time entry
		$entry = new TimeEntry($request->validated());
		$entry->user_id = $user->id;
		$entry->creator_user_id = $request->user()->id;
		$entry->event_id = $event->id;
		$entry->save();

		return $request->expectsJson()
			? response()->json(['time_entry' => $entry])
			: redirect()->back()->withSuccess('Created time entry.');
	}

	/**
	 * Delete a time entry
	 */
	public function destroyTimeEntry(Request $request, TimeEntry $timeEntry): JsonResponse {
		$this->authorize('delete', $timeEntry);

		$timeEntry->delete();

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess('Deleted time entry.');
	}

	/**
	 * Display the lockdown notice
	 */
	public function lockdown(): InertiaResponse|RedirectResponse {
		if (!Setting::isLockedDown()) return redirect()->route('volunteer.index');
		return Inertia::render('Maintenance');
	}
}
