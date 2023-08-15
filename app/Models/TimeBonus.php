<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeBonus extends UuidModel {
	use LogsActivity;

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

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['start', 'stop', 'modifier', 'department_id'])
			->logOnlyDirty()
			->submitEmptyLogs();
	}

	/**
	 * Get the department this time bonus is for
	 */
	public function department(): BelongsTo {
		return $this->belongsTo(Department::class);
	}
}
