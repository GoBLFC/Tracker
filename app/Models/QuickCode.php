<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickCode extends UuidModel {
	use MassPrunable;

	protected static function boot() {
		parent::boot();

		// Add a listener for the model being created to add a random code if one hasn't already been specified
		static::creating(function ($model) {
			if (!isset($model->code)) $model->generateCode();
		});
	}

	/**
	 * Get the user this quick code is for
	 */
	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}

	/**
	 * Get the prunable model query.
	 */
	public function prunable(): Builder {
		return static::expired();
	}

	/**
	 * Scope a query to only include unexpired codes
	 */
	public function scopeUnexpired(Builder $query): void {
		$query->where('updated_at', '>', now()->subSeconds(30));
	}

	/**
	 * Scope a query to only include expired codes
	 */
	public function scopeExpired(Builder $query): void {
		$query->where('updated_at', '<=', now()->subSeconds(30));
	}

	/**
	 * Check whether the code is expired
	 */
	public function isExpired(): bool {
		return $this->updated_at->lte(now()->subSeconds(30));
	}

	/**
	 * Generates and assigns a new code
	 */
	public function generateCode(): void {
		$this->code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
	}
}
