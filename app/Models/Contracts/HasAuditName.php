<?php

namespace App\Models\Contracts;

interface HasAuditName {
	/**
	 * Get the detailed name of the entity for auditing purposes
	 */
	public function getAuditNameAttribute(): string;
}
