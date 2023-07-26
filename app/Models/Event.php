<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends UuidModel {
	/**
	 * Get the time entries associated with this event
	 */
	public function timeEntries(): HasMany {
		return $this->hasMany(TimeEntry::class);
	}
}
