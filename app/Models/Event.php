<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\TimeEntry>|\App\Models\TimeEntry[] $timeEntries
 * @property-read int|null $time_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\TimeBonus>|\App\Models\TimeBonus[] $timeBonuses
 * @property-read int|null $time_bonuses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Reward>|\App\Models\Reward[] $rewards
 * @property-read int|null $rewards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\RewardClaim>|\App\Models\RewardClaim[] $rewardClaims
 * @property-read int|null $reward_claims_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Activity>|\App\Models\Activity[] $activities
 * @property-read int|null $activities_count
 *
 * @method static \Database\Factories\EventFactory<self> factory($count = null, $state = [])
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\Event firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\Event firstOrFail($columns = ['*'])
 * @method \App\Models\Event firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\Event firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\Event firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\Event updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class Event extends UuidModel {
	use HasFactory, SoftDeletes, LogsActivity;

	protected $fillable = [
		'name',
	];

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['name'])
			->logOnlyDirty()
			->submitEmptyLogs();
	}

	/**
	 * Get the time entries associated with this event
	 */
	public function timeEntries(): HasMany {
		return $this->hasMany(TimeEntry::class);
	}

	/**
	 * Get the time bonuses associated with this event
	 */
	public function timeBonuses(): HasMany {
		return $this->hasMany(TimeBonus::class);
	}

	/**
	 * Get the rewards that are available for this event
	 */
	public function rewards(): HasMany {
		return $this->hasMany(Reward::class);
	}

	/**
	 * Get the reward claims that have been made for this event
	 */
	public function rewardClaims(): HasManyThrough {
		return $this->hasManyThrough(RewardClaim::class, Reward::class);
	}

	/**
	 * Makes this the active event
	 */
	public function makeActive(): void {
		Setting::set('active-event', $this);
	}
}
