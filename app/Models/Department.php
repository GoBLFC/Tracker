<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends UuidModel {
	use HasFactory, SoftDeletes, LogsActivity;

	protected $casts = [
		'hidden' => 'boolean',
	];

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['name', 'hidden'])
			->logOnlyDirty()
			->submitEmptyLogs();
	}

	/**
	 * Get the time bonuses associated with this department
	 */
	public function timeBonuses(): HasMany {
		return $this->hasMany(TimeBonus::class);
	}
}
