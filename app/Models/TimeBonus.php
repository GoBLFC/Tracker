<?php

namespace App\Models;

class TimeBonus extends UuidModel {
	protected $casts = [
		'start' => 'datetime',
		'stop' => 'datetime',
	];

	/**
	 * Get the department this time bonus is for
	 */
	public department(): BelongsTo {
		return $this->belongsTo(Department::class);
	}
}
