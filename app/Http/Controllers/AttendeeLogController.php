<?php

namespace App\Http\Controllers;

use App\Facades\ConCat;
use App\Http\Requests\AttendeeLogStoreRequest;
use App\Http\Requests\AttendeeLogUpdateRequest;
use App\Http\Requests\AttendeeLogUserStoreRequest;
use App\Models\AttendeeLog;
use App\Models\AttendeeType;
use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Reports\Report;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class AttendeeLogController extends Controller {
	protected const REGISTRATION_LEVELS_CACHE_KEY = 'concat:registration-levels';

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

		if ($request->expectsJson()) return response()->json(['attendee_log' => $attendeeLog]);

		$props = [
			'attendeeLog' => $attendeeLog->load(['users' => function ($query) {
				$query->select('id', 'badge_id', 'badge_name')->withPivot('type', 'created_at');
			}]),
			'event' => fn () => $attendeeLog->event,
			'exportTypes' => fn () => Report::EXPORT_FILE_TYPES,
		];

		// Only users that can change the allowed registration levels need the list of levels to pick from
		if ($request->user()->can('update', $attendeeLog)) {
			$props['registrationLevels'] = Inertia::defer(fn () => $this->getRegistrationLevels());
		}

		return Inertia::render('AttendeeLogDetails', $props);
	}

	/**
	 * Clear the cached list of registration levels so the next request retrieves it from ConCat
	 */
	public function refreshRegistrationLevels(Request $request): JsonResponse|RedirectResponse {
		$this->authorize('create', AttendeeLog::class);

		Cache::forget(static::REGISTRATION_LEVELS_CACHE_KEY);

		return $request->expectsJson()
			? response()->json(['registration_levels' => $this->getRegistrationLevels()])
			: redirect()->back();
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

		// Check for an existing entry first so that attendees already in the log aren't checked again
		if ($user && $attendeeLog->users()->whereUserId($user->id)->wherePivot('type', $type)->exists()) {
			return $this->alreadyPresentResponse($request, $type, $user);
		}

		// Attendees must meet at least one of the log's entry requirements. The requirements based on Tracker data
		// (staff role and volunteer hours) are checked first so that ConCat is only contacted when necessary.
		$checkRequirements = $type === 'attendee' && $attendeeLog->hasEntryRequirements();
		$allowedByTrackerData = $checkRequirements && $user && $attendeeLog->allowsUserByTrackerData($user);
		$needsRegistration = !$user
			|| ($checkRequirements && !$allowedByTrackerData && $attendeeLog->isRestrictedByRegistrationLevel());

		// The ConCat registration is needed to create a user that isn't in the DB yet, or to check a registration level
		$registration = null;
		$registrationMissing = false;
		if ($needsRegistration) {
			try {
				ConCat::authorize();
				$registration = ConCat::getRegistration($badgeId);
			} catch (Throwable $err) {
				// A 404 means the badge has no registration, as opposed to ConCat being unreachable
				$registrationMissing = $err instanceof ClientException && $err->getResponse()->getStatusCode() === 404;
				if (!$registrationMissing) {
					Log::warning('Failed to look up ConCat registration for attendee log entry', [
						'badge_id' => $badgeId,
						'error' => $err,
					]);
				}

				// Without a registration, a badge that isn't in the DB can't be logged at all. An existing user only
				// needed it for the level check, so they're denied below instead.
				if (!$user) {
					return $request->expectsJson()
						? response()->json(['error' => "No registered attendee found with badge #{$badgeId}."], 404)
						: redirect()->back()->withErrors(['badge_id' => "No registered attendee found with badge #{$badgeId}."]);
				}
			}
		}

		// Users that are about to be created can't have any volunteer hours or a staff role yet, so the registration
		// level is the only requirement left that could let them in
		$failsRequirements = $checkRequirements
			&& !$allowedByTrackerData
			&& !($registration
				&& $attendeeLog->isRestrictedByRegistrationLevel()
				&& $attendeeLog->allowsRegistration($registration));

		if ($failsRequirements) {
			$error = $this->buildEntryDeniedMessage($attendeeLog, $badgeId, $user, $registration, $registrationMissing);
			return $request->expectsJson()
				? response()->json(['error' => $error], 403)
				: redirect()->back()->withErrors(['requirements' => $error]);
		}

		if (!$user) {
			$user = User::createFromConCatRegistration($registration, Role::Attendee);
		} elseif ($attendeeLog->users()->whereUserId($user->id)->wherePivot('type', $type)->exists()) {
			// Check again in case the user was added to the log while the requirements were being checked
			return $this->alreadyPresentResponse($request, $type, $user);
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
	 * Builds the response for a user that's already present in an attendee log
	 */
	protected function alreadyPresentResponse(Request $request, string $type, User $user): JsonResponse|RedirectResponse {
		$error = Str::title($type) . " {$user->audit_name} is already present in the log.";
		return $request->expectsJson()
			? response()->json(['error' => $error], 422)
			: redirect()->back()->withErrors(['badge_id' => $error]);
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
	 * Builds a message explaining which of an attendee log's entry requirements an attendee failed to meet
	 */
	protected function buildEntryDeniedMessage(
		AttendeeLog $attendeeLog,
		int $badgeId,
		?User $user,
		?\stdClass $registration,
		bool $registrationMissing,
	): string {
		$badgeName = $registration?->badgeName ?? $user?->badge_name;
		$who = $badgeName ? "{$badgeName} (#{$badgeId})" : "Badge #{$badgeId}";

		$reasons = [];
		$levelUnknown = $attendeeLog->isRestrictedByRegistrationLevel() && !$registration;
		if ($levelUnknown && $registrationMissing) {
			$reasons[] = "doesn't have a ConCat registration";
		} elseif ($levelUnknown) {
			$reasons[] = "couldn't have their registration level checked with ConCat";
		} elseif ($attendeeLog->isRestrictedByRegistrationLevel()) {
			$level = $registration->productDisplayName ?? $registration->productName ?? 'unknown';
			$reasons[] = "is registered as {$level}";
		}
		if ($attendeeLog->allow_staff) $reasons[] = "isn't staff";
		if ($attendeeLog->min_volunteer_hours !== null) {
			$hours = $user ? $attendeeLog->getVolunteerHours($user) : 0;
			$reasons[] = sprintf(
				'has %s of %s required volunteer hours',
				static::formatHours(floor($hours * 10) / 10),
				static::formatHours($attendeeLog->min_volunteer_hours),
			);
		}

		// A registration level on its own doesn't explain the denial, so state that the level isn't allowed
		$onlyLevel = count($reasons) === 1 && $attendeeLog->isRestrictedByRegistrationLevel() && !$levelUnknown;
		$joined = $onlyLevel
			? "{$reasons[0]}, which isn't allowed in this log"
			: Arr::join($reasons, ', ', count($reasons) > 2 ? ', and ' : ' and ');

		return "Denied: {$who} {$joined}.";
	}

	/**
	 * Formats an amount of hours without unnecessary trailing zeroes (12, 9.5, 11.25)
	 */
	protected static function formatHours(float $hours): string {
		return rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.');
	}

	/**
	 * Gets the distinct registration levels (products) from all ConCat registrations.
	 * The list is cached because building it requires paging through every registration.
	 * Returns null if ConCat can't be reached. Failures aren't cached.
	 *
	 * @return array<array{id: string, name: string, display_name: string|null}>|null
	 */
	protected function getRegistrationLevels(): ?array {
		try {
			return Cache::remember(static::REGISTRATION_LEVELS_CACHE_KEY, now()->addHours(6), function () {
				ConCat::authorize();
				return collect(ConCat::searchRegistrations(['limit' => 100]))
					->filter(fn ($registration) => isset($registration->productId, $registration->productName))
					->unique('productId')
					->map(fn ($registration) => [
						'id' => (string) $registration->productId,
						'name' => $registration->productName,
						'display_name' => $registration->productDisplayName ?? null,
					])
					->sortBy(fn ($level) => mb_strtolower($level['display_name'] ?? $level['name']))
					->values()
					->all();
			});
		} catch (Throwable $err) {
			Log::warning('Failed to retrieve registration levels from ConCat', ['error' => $err]);
			return null;
		}
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
