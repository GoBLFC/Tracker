<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends UuidModel {
	use HasFactory;

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
}
