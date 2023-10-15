<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use App\Models\Event;
use App\Facades\ConCat;
use App\Models\Setting;
use Illuminate\View\View;
use App\Models\AttendeeLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AttendeeLogStoreRequest;
use App\Http\Requests\AttendeeLogUpdateRequest;
use App\Http\Requests\AttendeeLogUserStoreRequest;

class AttendeeLogController extends Controller {
	/**
	 * List all attendee logs
	 */
	public function index(Request $request, ?Event $event = null): JsonResponse|View {
		if (!$event) $event = Setting::activeEvent();

		$query = $request->user()->can('viewAny', AttendeeLog::class)
			? AttendeeLog::forEvent($event)
			: $request->user()->attendeeLogs()->forEvent($event)->wherePivot('type', 'gatekeeper');
		$logs = $query->withCount(['users', 'attendees', 'gatekeepers'])->get();

		return $request->expectsJson()
			? response()->json(['attendee_logs' => $logs])
			: view('attendee-logs.list', [
				'event' => $event,
				'events' => Event::orderBy('name')->get(),
				'attendeeLogs' => $logs,
			]);
	}

	/**
	 * View an attendee log
	 */
	public function show(AttendeeLog $attendeeLog): JsonResponse|View {
		$this->authorize('view', $attendeeLog);
		return request()->expectsJson()
			? response()->json(['attendee_log' => $attendeeLog])
			: view('attendee-logs.view', ['attendeeLog' => $attendeeLog]);
	}

	/**
	 * Create an attendee log
	 */
	public function store(AttendeeLogStoreRequest $request, Event $event): JsonResponse {
		$attendeeLog = new AttendeeLog($request->validated());
		$attendeeLog->event_id = $event->id;
		$attendeeLog->save();
		return response()->json(['attendee_log' => $attendeeLog]);
	}

	/**
	 * Update an attendee log
	 */
	public function update(AttendeeLogUpdateRequest $request, AttendeeLog $attendeeLog): JsonResponse {
		$attendeeLog->update($request->safe());
		return response()->json(['attendee_log' => $attendeeLog]);
	}

	/**
	 * Delete an attendee log
	 */
	public function destroy(AttendeeLog $attendeeLog): JsonResponse {
		$this->authorize('delete', $attendeeLog);
		$attendeeLog->delete();
		return response()->json(null, 205);
	}

	/**
	 * Add a user to an attendee log
	 */
	public function storeUser(AttendeeLogUserStoreRequest $request, AttendeeLog $attendeeLog): JsonResponse {
		// Authorize the change
		$type = $request->validated('type') ?? 'attendee';
		$policyType = Str::pluralStudly($type, 2);
		$this->authorize("manage{$policyType}", $attendeeLog);

		// Only allow the change if the log belongs to the active event or the user is an admin
		if (!$attendeeLog->event->isActive() && !$request->user()->isAdmin()) {
			return response()->json(['error' => 'The event the log is for isn\'t active.'], 422);
		}

		// Find an existing user for the badge ID in the DB
		$badgeId = $request->validated('badge_id');
		$user = User::whereBadgeId($badgeId)->first();

		// If there isn't a user in the DB, then we retrieve registration details for the badge ID from ConCat
		// and create a user with that information.
		if (!$user) {
			try {
				ConCat::authorize();
				$registration = ConCat::getRegistration($badgeId);
			} catch (Throwable $err) {
				Log::warning('Failed to look up ConCat registration for attendee log entry', [
					'badge_id' => $badgeId,
					'error' => $err,
				]);
				return response()->json(['error' => 'Unable to find user with given badge ID.'], 404);
			}

			$user = User::createFromConCatRegistration($registration);
		}

		// Make sure the user isn't already present in the log
		if ($attendeeLog->users()->whereUserId($user->id)->exists()) {
			return response()->json(['error' => 'User is already present in the log.'], 422);
		}

		$attendeeLog->users()->attach($user, ['type' => $type]);
		return response()->json([
			'user' => $user->setVisible(['id', 'badge_id', 'badge_name']),
			'type' => $type,
			'logged_at' => now()->timezone(config('tracker.timezone'))->toDayDateTimeString(),
		]);
	}

	/**
	 * Remove a user from an attendee log
	 */
	public function destroyUser(Request $request, AttendeeLog $attendeeLog, User $user): JsonResponse {
		// Make sure the user is in the log
		$logUser = $attendeeLog->users()->whereUserId($user->id)->first();
		if (!$logUser) return response()->json(null, 404);

		// Authorize this change
		$policyType = Str::pluralStudly($logUser->pivot->type, 2);
		$this->authorize("manage{$policyType}", $attendeeLog);

		// Only allow the change if the log belongs to the active event or the user is an admin
		if (!$attendeeLog->event->isActive() && !$request->user()->isAdmin()) {
			return response()->json(['error' => 'The event the log is for isn\'t active.'], 422);
		}

		$attendeeLog->users()->detach($user);
		return response()->json(null, 205);
	}
}
