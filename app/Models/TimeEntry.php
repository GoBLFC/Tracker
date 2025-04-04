<?php

namespace App\Models;

use App\Models\Traits\ChecksActiveEvent;
use Carbon\CarbonInterval;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property string $user_id
 * @property \Illuminate\Support\Carbon $start
 * @property \Illuminate\Support\Carbon|null $stop
 * @property string $department_id
 * @property string|null $notes
 * @property string|null $creator_user_id
 * @property bool $auto
 * @property string $event_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read int|null $users_count
 * @property-read \App\Models\Department|null $department
 * @property-read int|null $departments_count
 * @property-read \App\Models\User|null $creator
 * @property-read int|null $creators_count
 * @property-read \App\Models\Event|null $event
 * @property-read int|null $events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Activity>|\App\Models\Activity[] $activities
 * @property-read int|null $activities_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry ongoing()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry forEvent(\App\Models\Event|string|null $event = null)
 * @method static \Database\Factories\TimeEntryFactory<self> factory($count = null, $state = [])
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\TimeEntry firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\TimeEntry firstOrFail($columns = ['*'])
 * @method \App\Models\TimeEntry firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\TimeEntry firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\TimeEntry firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\TimeEntry updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class TimeEntry extends Model {
	/** @use HasFactory<\Database\Factories\TimeEntryFactory> */
	use ChecksActiveEvent, HasFactory, HasUuids, LogsActivity;

	protected $casts = [
		'start' => 'datetime',
		'stop' => 'datetime',
		'auto' => 'boolean',
	];
	protected $fillable = [
		'user_id',
		'start',
		'stop',
		'department_id',
		'notes',
		'creator_user_id',
		'auto',
		'event_id',
	];

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['start', 'stop', 'notes', 'auto', 'department_id', 'user_id', 'event_id'])
			->logOnlyDirty()
			->submitEmptyLogs();
	}

	/**
	 * Get the user this time entry is for
	 */
	public function user(): BelongsTo {
		return $this->belongsTo(User::class)->withTrashed();
	}

	/**
	 * Get the department this timem entry is for
	 */
	public function department(): BelongsTo {
		return $this->belongsTo(Department::class)->withTrashed();
	}

	/**
	 * Get the user that created this time entry
	 */
	public function creator(): BelongsTo {
		return $this->belongsTo(User::class, null, 'creator_user_id')->withTrashed();
	}

	/**
	 * Get the event this time entry is for
	 */
	public function event(): BelongsTo {
		return $this->belongsTo(Event::class)->withTrashed();
	}

	/**
	 * Scope a query to only include ongoing entries
	 */
	public function scopeOngoing(Builder $query): void {
		$query->whereNull('stop');
	}

	/**
	 * Scope a query to only include entries for an event.
	 * If the event is not specified, then the active event will be used.
	 */
	public function scopeForEvent(Builder $query, Event|string|null $event = null): void {
		$query->where('event_id', $event->id ?? $event ?? Setting::activeEvent()?->id);
	}

	/**
	 * Check whether this time entry is still ongoing (has no stop time)
	 */
	public function isOngoing() {
		return !$this->stop;
	}

	/**
	 * Calculate the duration (in seconds) of the time entry.
	 * If it's ongoing, it will be calculated up to the current time.
	 */
	public function getDuration(): float {
		return $this->start->diffInSeconds($this->stop ?? now());
	}

	/**
	 * Get a human-friendly representation of the time entry's duration (formatted like "6h 40m")
	 */
	public function getHumanDuration(): string {
		return static::humanDuration($this->getDuration());
	}

	/**
	 * Get a clock-like representation of the time entry's duration (formatted like "5:06:32")
	 */
	public function getClockDuration(): string {
		return static::clockDuration($this->getDuration());
	}

	/**
	 * Get the start time of the time entry in the tracker's timezone, offset by the day boundary hour.
	 * Useful mainly for day comparisons.
	 *
	 * @param int $direction Direction of the offset (1 or -1)
	 * @param ?int [$dayBoundaryHour] Hour of the day to use as the day boundary (defaults to the configured hour)
	 */
	public function getBoundaryOffsetStart(int $direction, ?int $dayBoundaryHour = null): Carbon {
		if (!$dayBoundaryHour) $dayBoundaryHour = config('tracker.day_boundary_hour');
		return $this->start->avoidMutation()
			->timezone(config('tracker.timezone'))
			->addHours($dayBoundaryHour * $direction);
	}

	/**
	 * Get the stop time of the time entry in the tracker's timezone, offset by the day boundary hour.
	 * If the time entry is still ongoing, the stop time will be considered to be the current time.
	 * Useful mainly for day comparisons.
	 *
	 * @param int $direction Direction of the offset (1 or -1)
	 * @param ?int [$dayBoundaryHour] Hour of the day to use as the day boundary (defaults to the configured hour)
	 */
	public function getBoundaryOffsetStop(int $direction, ?int $dayBoundaryHour = null): Carbon {
		if (!$dayBoundaryHour) $dayBoundaryHour = config('tracker.day_boundary_hour');
		$tz = config('tracker.timezone');
		return ($this->stop?->avoidMutation()?->timezone($tz) ?? now($tz))
			->addHours($dayBoundaryHour * $direction);
	}

	/**
	 * Check whether the time entry crosses the day boundary
	 */
	public function isCrossingDayBoundary(?int $dayBoundaryHour = null): bool {
		return !$this->getBoundaryOffsetStart(-1, $dayBoundaryHour)
			->isSameDay($this->getBoundaryOffsetStop(-1, $dayBoundaryHour));
	}

	/**
	 * Get the amount of time the entry has run (in seconds) since the day boundary.
	 * If it doesn't cross the day boundary at all, then this will return 0.
	 */
	public function getSecondsPastDayBoundary(?int $dayBoundaryHour = null): float {
		if (!$dayBoundaryHour) $dayBoundaryHour = config('tracker.day_boundary_hour');
		if (!$this->isCrossingDayBoundary($dayBoundaryHour)) return 0;

		$tz = config('tracker.timezone');
		$stop = $this->stop?->avoidMutation()?->timezone($tz) ?? now($tz);
		return $stop->avoidMutation()
			->startOfDay()
			->addHours($dayBoundaryHour)
			->diffInSeconds($stop);
	}

	/**
	 * Calculates how much bonus time (in seconds) to reward from a set of bonuses.
	 * Supports stacking bonuses and takes the department into account.
	 *
	 * @param ?Event $event Event to get the earned time for - if null, then the active event will be used
	 * @param ?Collection $bonuses Bonuses to look through (to avoid extra queries if already retrieved)
	 */
	public function calculateBonusTime(?Event $event = null, ?Collection $bonuses = null): float {
		// Retrieve a list of potentially applicable bonuses if they haven't been supplied
		if (!$bonuses) {
			$bonuses = TimeBonus::forEvent($event)
				->whereRelation('departments', 'id', $this->department_id)
				->get();
		}

		if ($bonuses->count() <= 0) return 0;

		// Total up all applicable bonus time
		$bonusTotal = 0;
		foreach ($bonuses as $bonus) {
			if ($bonus->pivot->department_id !== $this->department_id) continue;
			$bonusTime = static::calculateOverlap($this->start, $this->stop ?? now(), $bonus->start, $bonus->stop);
			$bonusTotal += round($bonusTime * ($bonus->modifier - 1));
		}

		return $bonusTotal;
	}

	/**
	 * Calculates the total time for the time entry, including bonuses
	 *
	 * @param ?Event $event Event to get the earned time for - if null, then the active event will be used
	 * @param ?Collection $bonuses Bonuses to look through (to avoid extra queries if already retrieved)
	 */
	public function calculateTotalTime(?Event $event = null, ?Collection $bonuses = null): float {
		return $this->getDuration() + $this->calculateBonusTime($event, $bonuses);
	}

	/**
	 * Calculates the overlap (in seconds) of two time periods
	 */
	public static function calculateOverlap(
		DateTimeInterface $timeBegin1,
		DateTimeInterface $timeEnd1,
		DateTimeInterface $timeBegin2,
		DateTimeInterface $timeEnd2
	): int {
		// Find the more recent (larger) starting time
		$overlapBegin = ($timeBegin1 < $timeBegin2) ? $timeBegin2 : $timeBegin1;

		// Find the older (smaller) ending time
		$overlapEnd = ($timeEnd1 < $timeEnd2) ? $timeEnd1 : $timeEnd2;

		// Get the difference - negative result means no overlap
		$overlap = $overlapEnd->getTimestamp() - $overlapBegin->getTimestamp();
		return $overlap > 0 ? $overlap : 0;
	}

	/**
	 * Get a human-friendly representation of a duration of time (formatted like "6h 40m")
	 */
	public static function humanDuration(int $seconds): string {
		return CarbonInterval::seconds($seconds)->cascade()->forHumans([
			'short' => true,
			'skip' => ['day'],
			'parts' => 2,
		]);
	}

	/**
	 * Get a clock-like representation of a duration of time (formatted like "5:06:32")
	 */
	public static function clockDuration(int $seconds): string {
		if ($seconds === 0) return '0:00';
		$interval = CarbonInterval::seconds($seconds)->cascade();
		$string = '';
		if ($interval->hours > 0) $string .= "{$interval->hours}:";
		$string .= ($interval->hours > 0 ? Str::padLeft($interval->minutes, 2, '0') : $interval->minutes) . ':';
		$string .= Str::padLeft($interval->seconds, 2, '0');
		return $string;
	}
}
