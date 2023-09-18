<?php

namespace App\Models\Contracts;

interface HasDisplayName {
	/**
	 * Get the user-friendly display name of the entity
	 */
	public function getDisplayNameAttribute(): string;
}
