<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use App\Models\Contracts\HasDisplayName;
use App\Models\Traits\ChecksActiveEvent;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $name
 * @property string $event_id
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
class AttendeeLog extends UuidModel implements HasDisplayName {
	use SoftDeletes, LogsActivity, ChecksActiveEvent;

	protected $fillable = [
		'name',
	];

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['name', 'event_id'])
			->logOnlyDirty()
			->submitEmptyLogs();
	}

	public function getDisplayNameAttribute(): string {
		return !$this->deleted_at ? $this->name : "{$this->name} (del)";
	}

	/**
	 * Get the event this attendee log is for
	 */
	public function event(): BelongsTo {
		return $this->belongsTo(Event::class)->withTrashed();
	}

	/**
	 * Get all of the users entered into this attendee log (attendees and gatekeepers alike)
	 */
	public function users(): BelongsToMany {
		return $this->belongsToMany(User::class)
			->withPivot('type')
			->withTimestamps()
			->withTrashed();
	}

	/**
	 * Get the attendees entered into this attendee log
	 */
	public function attendees(): BelongsToMany {
		return $this->users()->wherePivot('type', 'attendee');
	}

	/**
	 * Get the gatekeepers assigned to this attendee log
	 */
	public function gatekeepers(): BelongsToMany {
		return $this->users()->wherePivot('type', 'gatekeeper');
	}

	/**
	 * Scope a query to only include attendee logs for an event.
	 * If the event is not specified, then the active event will be used.
	 */
	public function scopeForEvent(Builder $query, Event|string $event = null): void {
		$query->where('event_id', $event->id ?? $event ?? Setting::activeEvent()?->id);
	}
}
