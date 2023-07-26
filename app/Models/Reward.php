<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends UuidModel {
	/**
	 * Get the event the reward is a part of
	 */
	public function event(): BelongsTo {
		return $this->belongsTo(Event::class);
	}
}
