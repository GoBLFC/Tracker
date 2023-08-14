<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends UuidModel {
	use HasFactory, SoftDeletes;

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
}
