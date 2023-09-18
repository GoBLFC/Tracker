<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property string $user_id
 * @property string $reward_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read int|null $users_count
 * @property-read \App\Models\Reward|null $reward
 * @property-read int|null $rewards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Activity>|\App\Models\Activity[] $activities
 * @property-read int|null $activities_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RewardClaim forEvent(\App\Models\Event|string|null $event = null)
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\RewardClaim firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\RewardClaim firstOrFail($columns = ['*'])
 * @method \App\Models\RewardClaim firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\RewardClaim firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\RewardClaim firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\RewardClaim updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class RewardClaim extends UuidModel {
	use LogsActivity;

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['user_id', 'reward_id'])
			->submitEmptyLogs();
	}

	/**
	 * Get the user that made this reward claim
	 */
	public function user(): BelongsTo {
		return $this->belongsTo(User::class)->withTrashed();
	}

	/**
	 * Get the reward this claim is for
	 */
	public function reward(): BelongsTo {
		return $this->belongsTo(Reward::class)->withTrashed();
	}

	/**
	 * Scope a query to only include reward claims for an event.
	 * If the event is not specified, then the active event will be used.
	 */
	public function scopeForEvent(Builder $query, Event|string $event = null): void {
		$query->whereRelation('reward', 'event_id', $event->id ?? $event ?? Setting::activeEvent()?->id);
	}
}
