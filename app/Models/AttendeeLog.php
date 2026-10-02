<?php

namespace App\Models;

use App\Models\Contracts\HasDisplayName;
use App\Models\Traits\ChecksActiveEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property string $name
 * @property string $event_id
 * @property string[]|null $allowed_registration_levels
 * @property bool $allow_staff
 * @property float|null $min_volunteer_hours
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Event|null $event
 * @property-read int|null $events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\User>|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\User>|\App\Models\User[] $attendees
 * @property-read int|null $attendees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\User>|\App\Models\User[] $gatekeepers
 * @property-read int|null $gatekeepers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Activity>|\App\Models\Activity[] $activities
 * @property-read int|null $activities_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendeeLog forEvent(\App\Models\Event|string|null $event = null)
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\AttendeeLog firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\AttendeeLog firstOrFail($columns = ['*'])
 * @method \App\Models\AttendeeLog firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\AttendeeLog firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\AttendeeLog firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\AttendeeLog updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class AttendeeLog extends Model implements HasDisplayName {
	use ChecksActiveEvent, HasUuids, LogsActivity, SoftDeletes;

	protected $fillable = [
		'name',
		'allowed_registration_levels',
		'allow_staff',
		'min_volunteer_hours',
	];
	protected $casts = [
		'allowed_registration_levels' => 'array',
		'allow_staff' => 'boolean',
		'min_volunteer_hours' => 'float',
	];

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly([
				'name',
				'event_id',
				'allowed_registration_levels',
				'allow_staff',
				'min_volunteer_hours',
			])
			->logOnlyDirty()
			->submitEmptyLogs();
	}

	public function getDisplayNameAttribute(): string {
		return !$this->deleted_at ? $this->name : "{$this->name} (del)";
	}

	/**
	 * Get the event this attendee log is for
	 *
	 * @return BelongsTo<Event, AttendeeLog>
	 */
	public function event(): BelongsTo {
		return $this->belongsTo(Event::class)->withTrashed();
	}

	/**
	 * Get all of the users entered into this attendee log (attendees and gatekeepers alike)
	 *
	 * @return BelongsToMany<User, AttendeeLog>
	 */
	public function users(): BelongsToMany {
		return $this->belongsToMany(User::class)
			->withPivot('type')
			->withTimestamps()
			->withTrashed();
	}

	/**
	 * Get the attendees entered into this attendee log
	 *
	 * @return BelongsToMany<User, AttendeeLog>
	 */
	public function attendees(): BelongsToMany {
		return $this->users()->wherePivot('type', 'attendee');
	}

	/**
	 * Get the gatekeepers assigned to this attendee log
	 *
	 * @return BelongsToMany<User, AttendeeLog>
	 */
	public function gatekeepers(): BelongsToMany {
		return $this->users()->wherePivot('type', 'gatekeeper');
	}

	/**
	 * Scope a query to only include attendee logs for an event.
	 * If the event is not specified, then the active event will be used.
	 *
	 * @param Builder<AttendeeLog> $query
	 */
	public function scopeForEvent(Builder $query, Event|string|null $event = null): void {
		$query->where('event_id', $event->id ?? $event ?? Setting::activeEvent()?->id);
	}

	/**
	 * Checks whether this attendee log has a specific user as an attendee
	 */
	public function hasAttendee(User|string $user): bool {
		return $this->attendees()->whereUserId($user->id ?? $user)->exists();
	}

	/**
	 * Checks whether this attendee log has any entry requirements for attendees.
	 * Attendees pass if they meet any one of the requirements that are set.
	 */
	public function hasEntryRequirements(): bool {
		return $this->isRestrictedByRegistrationLevel() || $this->allow_staff || $this->min_volunteer_hours !== null;
	}

	/**
	 * Checks whether a user meets any of the entry requirements that only rely on Tracker data (staff role and
	 * volunteer hours), so their ConCat registration doesn't need to be retrieved to check them
	 */
	public function allowsUserByTrackerData(User $user): bool {
		if ($this->allow_staff && $user->isStaff()) return true;
		if ($this->min_volunteer_hours !== null && $this->getVolunteerHours($user) >= $this->min_volunteer_hours) {
			return true;
		}
		return false;
	}

	/**
	 * Gets the volunteer hours a user has earned (including bonuses) for this attendee log's event
	 */
	public function getVolunteerHours(User $user): float {
		return $user->getEarnedTime($this->event) / 3600;
	}

	/**
	 * Checks whether this attendee log only accepts attendees with specific registration levels
	 */
	public function isRestrictedByRegistrationLevel(): bool {
		return !empty($this->allowed_registration_levels);
	}

	/**
	 * Checks whether a ConCat registration is allowed into this attendee log.
	 * A registration is allowed if its product ID or product name matches an allowed level (case-insensitive).
	 * Unrestricted logs allow every registration.
	 */
	public function allowsRegistration(\stdClass $registration): bool {
		if (!$this->isRestrictedByRegistrationLevel()) return true;

		$candidates = array_map(
			fn ($value) => mb_strtolower(trim((string) $value)),
			array_filter([$registration->productId ?? null, $registration->productName ?? null]),
		);
		foreach ($this->allowed_registration_levels as $level) {
			if (in_array(mb_strtolower(trim($level)), $candidates, true)) return true;
		}

		return false;
	}

	/**
	 * Checks whether this attendee log has a specific user as a gatekeeper
	 */
	public function hasGatekeeper(User|string $user): bool {
		return $this->gatekeepers()->whereUserId($user->id ?? $user)->exists();
	}
}
