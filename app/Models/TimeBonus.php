<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property \Illuminate\Support\Carbon $start
 * @property \Illuminate\Support\Carbon $stop
 * @property float $modifier
 * @property string $event_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Event|null $event
 * @property-read int|null $events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Department>|\App\Models\Department[] $departments
 * @property-read int|null $departments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Activity>|\App\Models\Activity[] $activities
 * @property-read int|null $activities_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeBonus forEvent(\App\Models\Event|string|null $event = null)
 * @method static \Database\Factories\TimeBonusFactory<self> factory($count = null, $state = [])
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\TimeBonus firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\TimeBonus firstOrFail($columns = ['*'])
 * @method \App\Models\TimeBonus firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\TimeBonus firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\TimeBonus firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\TimeBonus updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class TimeBonus extends Model {
	/** @use HasFactory<\Database\Factories\TimeBonusFactory> */
	use HasFactory, HasUuids, LogsActivity;

	protected $casts = [
		'start' => 'datetime',
		'stop' => 'datetime',
		'modifier' => 'float',
	];
	protected $fillable = [
		'start',
		'stop',
		'modifier',
	];

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['start', 'stop', 'modifier', 'event_id'])
			->logOnlyDirty()
			->submitEmptyLogs();
	}

	/**
	 * Get the event this time bonus is for
	 */
	public function event(): BelongsTo {
		return $this->belongsTo(Event::class)->withTrashed();
	}

	/**
	 * Get the departments this time bonus affects
	 */
	public function departments(): BelongsToMany {
		return $this->belongsToMany(Department::class)->withTrashed();
	}

	/**
	 * Scope a query to only include bonuses for an event.
	 * If the event is not specified, then the active event will be used.
	 */
	public function scopeForEvent(Builder $query, Event|string|null $event = null): void {
		$query->where('event_id', $event->id ?? $event ?? Setting::activeEvent()?->id);
	}
}
