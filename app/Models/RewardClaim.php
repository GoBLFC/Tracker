<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardClaim extends UuidModel {
	/**
	 * Get the user that made this reward claim
	 */
	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}

	/**
	 * Get the reward this claim is for
	 */
	public function reward(): BelongsTo {
		return $this->belongsTo(Reward::class);
	}
}
