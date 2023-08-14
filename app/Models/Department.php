<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends UuidModel {
	use HasFactory, SoftDeletes;

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
