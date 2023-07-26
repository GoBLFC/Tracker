<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends UuidModel {
	protected $casts = [
		'has_read' => 'boolean',
	];

	/**
	 * Get the user this notification is for
	 */
	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}

	/**
	 * Get the reward this notification is for (if applicable)
	 */
	public function reward(): BelongsTo {
		return $this->belongsTo(Reward::class);
	}

	/**
	 * Scope a query to only include unread notifications
	 */
	public function scopeUnread(Builder $query): void {
		$query->where('has_read', false);
	}
}
