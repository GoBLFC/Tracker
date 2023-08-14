<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\UuidModel;
use Carbon\CarbonInterval;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeEntry extends UuidModel {
	use HasFactory;

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

	/**
	 * Get the user this time entry is for
	 */
	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}

	/**
	 * Get the department this timem entry is foro
	 */
	public function department(): BelongsTo {
		return $this->belongsTo(Department::class);
	}

	/**
	 * Get the user that created this time entry
	 */
	public function creator(): BelongsTo {
		return $this->belongsTo(User::class, null, 'creator_user_id');
	}

	/**
	 * Get the event this time entry is for
	 */
	public function event(): BelongsTo {
		return $this->belongsTo(Event::class);
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
	public function scopeForEvent(Builder $query, Event|string $event = null): void {
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
	 * @return integer
	 */
	public function getDuration(): int {
		return ($this->stop ?? now())->diffInSeconds($this->start);
	}

	/**
	 * Get a human-friendly representation of the time entry's duration (formatted like "6h 40m")
	 */
	public function getHumanDuration(): string {
		return static::humanDuration($this->getDuration());
	}

	/**
	 * Get a clock-like representation of the time entry's duration (formatted like "5:06:32")
	 *
	 * @return string
	 */
	public function getClockDuration(): string {
		return static::clockDuration($this->getDuration());
	}

	/**
	 * Get the start time of the time entry, offset by the day boundary hour.
	 * Useful mainly for day comparisons.
	 *
	 * @param integer $direction Direction of the offset (1 or -1)
	 * @param ?integer [$dayBoundaryHour] Hour of the day to use as the day boundary (defaults to the configured hour)
	 */
	public function getBoundaryOffsetStart(int $direction, int $dayBoundaryHour = null): Carbon {
		if (!$dayBoundaryHour) $dayBoundaryHour = config('tracker.day_boundary_hour');
		return $this->start->avoidMutation()->addHours($dayBoundaryHour * $direction);
	}

	/**
	 * Get the stop time of the time entry, offset by the day boundary hour.
	 * If the time entry is still ongoing, the stop time will be considered to be the current time.
	 * Useful mainly for day comparisons.
	 *
	 * @param integer $direction Direction of the offset (1 or -1)
	 * @param ?integer [$dayBoundaryHour] Hour of the day to use as the day boundary (defaults to the configured hour)
	 */
	public function getBoundaryOffsetStop(int $direction, int $dayBoundaryHour = null): Carbon {
		if (!$dayBoundaryHour) $dayBoundaryHour = config('tracker.day_boundary_hour');
		return ($this->stop?->avoidMutation() ?? now())->addHours($dayBoundaryHour * $direction);
	}

	/**
	 * Check whether the time entry crosses the day boundary
	 */
	public function isCrossingDayBoundary(int $dayBoundaryHour = null): bool {
		return !$this->getBoundaryOffsetStart(-1, $dayBoundaryHour)
			->isSameDay($this->getBoundaryOffsetStop(-1, $dayBoundaryHour));
	}

	/**
	 * Get the amount of time the entry has run (in seconds) since the day boundary.
	 * If it doesn't cross the day boundary at all, then this will return 0.
	 */
	public function getSecondsPastDayBoundary(int $dayBoundaryHour = null): int {
		if (!$dayBoundaryHour) $dayBoundaryHour = config('tracker.day_boundary_hour');
		if (!$this->isCrossingDayBoundary($dayBoundaryHour)) return 0;

		$stop = $this->stop ?? now();
		return $stop->diffInSeconds(
			$stop->avoidMutation()
				->startOfDay()
				->addHours($dayBoundaryHour)
		);
	}

	/**
	 * Calculates how much bonus time (in seconds) to reward from a set of bonuses.
	 * Supports stacking bonuses and takes the department into account.
	 */
	public function calculateBonusTime(Collection $bonuses = null): int {
		// Retrieve a list of potentially applicable bonuses if they haven't been supplied
		if (!$bonuses) $bonuses = TimeBonus::whereDepartmentId($this->department_id)->get();
		if ($bonuses->count() <= 0) return 0;

		// Total up all applicable bonus time
		$bonusTotal = 0;
		foreach ($bonuses as $bonus) {
			if ($bonus->department_id !== $this->department_id) continue;
			$bonusTime = static::calculateOverlap($this->start, $this->stop ?? now(), $bonus->start, $bonus->stop);
			$bonusTotal += round($bonusTime * ($bonus->modifier - 1));
		}

		return $bonusTotal;
	}

	/**
	 * Calculates the total time for the time entry, including bonuses
	 */
	public function calculateTotalTime(Collection $bonuses = null): int {
		return $this->getDuration() + $this->calculateBonusTime($bonuses);
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
		// Round down to the nearest minute
		$seconds -= $seconds % 60;
		// Intervals don't seem to support 0 values, instead becoming 1s
		if ($seconds === 0) return '0m';
		return CarbonInterval::seconds($seconds)->cascade()->forHumans(['short' => true]);
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
