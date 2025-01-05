<?php

namespace App\Http\Controllers;

use App\Facades\ConCat;
use App\Http\Requests\AttendeeLogStoreRequest;
use App\Http\Requests\AttendeeLogUpdateRequest;
use App\Http\Requests\AttendeeLogUserStoreRequest;
use App\Models\AttendeeLog;
use App\Models\AttendeeType;
use App\Models\Event;
use App\Models\Setting;
use App\Models\User;
use App\Reports\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class AttendeeLogController extends Controller {
	/**
	 * List all attendee logs
	 */
	public function index(Request $request, ?Event $event = null): JsonResponse|InertiaResponse {
		if (!$event) $event = Setting::activeEvent();
		$this->authorize('viewForEvent', [AttendeeLog::class, $event]);

		$canViewAnyEvent = $request->user()->can('viewAny', Event::class);
		$logs = $this->getVisibleLogs($event);

		return $request->expectsJson()
			? response()->json(['attendee_logs' => $logs])
			: Inertia::render('AttendeeLogIndex', [
				'attendeeLogs' => $logs,
				'event' => $event,
				'events' => fn () => $canViewAnyEvent ? Event::orderBy('name')->get() : null,
			]);
	}

	/**
	 * View an attendee log
	 */
	public function show(Request $request, AttendeeLog $attendeeLog): JsonResponse|InertiaResponse {
		$this->authorize('view', $attendeeLog);

		return $request->expectsJson()
			? response()->json(['attendee_log' => $attendeeLog])
			: Inertia::render('AttendeeLogDetails', [
				'attendeeLog' => $attendeeLog->load(['users' => function ($query) {
					$query->select('id', 'badge_id', 'badge_name')->withPivot('type', 'created_at');
				}]),
				'event' => fn () => $attendeeLog->event,
				'exportTypes' => fn () => Report::EXPORT_FILE_TYPES,
			]);
	}

	/**
	 * Create an attendee log
	 */
	public function store(AttendeeLogStoreRequest $request, Event $event): JsonResponse|RedirectResponse {
		$attendeeLog = new AttendeeLog($request->validated());
		$attendeeLog->event_id = $event->id;
		$attendeeLog->save();

		return $request->expectsJson()
			? response()->json(['attendee_log' => $attendeeLog])
			: redirect()->back()->withSuccess("Created attendee log {$attendeeLog->name}.");
	}

	/**
	 * Update an attendee log
	 */
	public function update(AttendeeLogUpdateRequest $request, AttendeeLog $attendeeLog): JsonResponse|RedirectResponse {
		$attendeeLog->update($request->validated());

		return $request->expectsJson()
			? response()->json(['attendee_log' => $attendeeLog])
			: redirect()->back()->withSuccess("Updated attendee log {$attendeeLog->name}.");
	}

	/**
	 * Delete an attendee log
	 */
	public function destroy(Request $request, AttendeeLog $attendeeLog): JsonResponse|RedirectResponse {
		$this->authorize('delete', $attendeeLog);

		$attendeeLog->delete();

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess("Deleted attendee log {$attendeeLog->name}.");
	}

	/**
	 * Add a user to an attendee log
	 */
	public function storeUser(AttendeeLogUserStoreRequest $request, AttendeeLog $attendeeLog): JsonResponse|RedirectResponse {
		// Authorize the change
		$type = $request->validated('type') ?? 'attendee';
		$policyType = Str::plural(Str::title($type), 2);
		$this->authorize("manage{$policyType}", $attendeeLog);

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
				return $request->expectsJson()
					? response()->json(['error' => 'Unable to find user with given badge number.'], 404)
					: redirect()->back()->withErrors(['badge_id' => 'Unable to find user with given badge number.']);
			}

			$user = User::createFromConCatRegistration($registration, 'Attendee');
		}

		// Make sure the user isn't already present in the log
		if ($attendeeLog->users()->whereUserId($user->id)->wherePivot('type', $type)->exists()) {
			$typeName = Str::title($type);
			return $request->expectsJson()
				? response()->json(['error' => "{$typeName} {$user->audit_name} is already present in the log."], 422)
				: redirect()->back()->withErrors(['badge_id' => "{$typeName} {$user->audit_name} is already present in the log."]);
		}

		$attendeeLog->users()->attach($user, ['type' => $type]);

		return $request->expectsJson()
			? response()->json([
				'user' => $user->setVisible(['id', 'badge_id', 'badge_name']),
				'type' => $type,
				'logged_at' => now()->timezone(config('tracker.timezone'))->toDayDateTimeString(),
			])
			: redirect()->back()->withSuccess("Added {$type} {$user->audit_name} to the log.");
	}

	/**
	 * Remove a user from an attendee log
	 */
	public function destroyUser(Request $request, AttendeeLog $attendeeLog, AttendeeType $type, User $user): JsonResponse|RedirectResponse {
		// Make sure the user is in the log
		$logUser = $attendeeLog->users()->whereUserId($user->id)->wherePivot('type', $type)->first();
		if (!$logUser) return response()->json(null, 404);

		// Authorize this change
		$policyType = Str::plural(Str::title($logUser->pivot->type), 2);
		$this->authorize("manage{$policyType}", $attendeeLog);

		$attendeeLog->users()->wherePivot('type', $type)->detach($user);

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess("Removed {$type->value} {$user->audit_name} from the log.");
	}

	/**
	 * Gets an event's attendee logs that are visible to the user
	 *
	 * @return Collection<string, AttendeeLog>
	 */
	protected function getVisibleLogs(?Event $event, ?User $user = null): Collection {
		if (!$user) $user = request()->user();

		$logsQuery = $user->can('viewAny', AttendeeLog::class)
			? AttendeeLog::forEvent($event)
			: $user->attendeeLogs()->forEvent($event)->wherePivot('type', 'gatekeeper');

		return $logsQuery->withCount(['users', 'attendees', 'gatekeepers'])->get();
	}
}
