<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickCode extends UuidModel {
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
}
