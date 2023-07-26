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
			if (!isset($model->code)) $model->code = random_int(0, 9999);
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
		return static::where('created_at', '<=', now()->subSeconds(30));
	}
}
