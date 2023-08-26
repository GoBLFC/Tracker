<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class RewardClaim extends UuidModel {
	use LogsActivity;

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->submitEmptyLogs();
	}

	/**
	 * Get the user that made this reward claim
	 */
	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}

	/**
	 * Get the reward this claim is for
	 */
	public function reward(): BelongsTo {
		return $this->belongsTo(Reward::class);
	}

	/**
	 * Scope a query to only include reward claims for an event.
	 * If the event is not specified, then the active event will be used.
	 */
	public function scopeForEvent(Builder $query, Event|string $event = null): void {
		$query->whereRelation('reward', 'event_id', $event->id ?? $event ?? Setting::activeEvent()?->id);
	}
}
