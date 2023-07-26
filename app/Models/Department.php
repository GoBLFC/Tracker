<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends UuidModel {
	use HasFactory;

	protected $casts = [
		'hidden' => 'boolean',
	];

	/**
	 * Get the time bonuses associated with this department
	 */
	public function timeBonuses(): HasMany {
		return $this->hasMany(TimeBonus::class);
	}
}
