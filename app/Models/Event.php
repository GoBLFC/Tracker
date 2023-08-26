<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Event extends UuidModel {
	use HasFactory, SoftDeletes, LogsActivity;

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
