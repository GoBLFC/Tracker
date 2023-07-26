<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends UuidModel {
	protected $casts = [
		'start' => 'timestamp',
		'stop' => 'timestamp',
		'auto' => 'boolean',
	];

	/**
	 * Get the user this time entry is for
	 */
	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
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
}
