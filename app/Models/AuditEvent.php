<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends UuidModel {
	/**
	 * Get the user that performed this event
	 */
	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}
}
