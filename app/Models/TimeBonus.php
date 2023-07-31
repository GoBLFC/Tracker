<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeBonus extends UuidModel {
	protected $casts = [
		'start' => 'datetime',
		'stop' => 'datetime',
	];

	protected $fillable = [
		'start',
		'stop',
		'modifier',
		'department_id',
	];

	/**
	 * Get the department this time bonus is for
	 */
	public function department(): BelongsTo {
		return $this->belongsTo(Department::class);
	}
}
