<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeBonus extends UuidModel {
	use HasFactory, LogsActivity;

	protected $casts = [
		'start' => 'datetime',
		'stop' => 'datetime',
		'modifier' => 'float',
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

	/**
	 * Scope a query to only include bonuses for an event.
	 * If the event is not specified, then the active event will be used.
	 */
	public function scopeForEvent(Builder $query, Event|string $event = null): void {
		$query->where('event_id', $event->id ?? $event ?? Setting::activeEvent()?->id);
	}
}
