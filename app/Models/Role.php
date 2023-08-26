<?php

namespace App\Models;

use Illuminate\Support\Str;

enum Role: int {
	case Admin = 3;
	case Manager = 2;
	case Lead = 1;
	case Volunteer = 0;
	case Banned = -1;

	/**
	 * Get the enumeration case with the specified name
	 */
	public static function fromName(string $roleName): static {
		$roleName = Str::title($roleName);
		$role = constant("static::{$roleName}");
		if ($role === null) throw new \ValueError("Unknown role name: {$roleName}");
		return $role;
	}
}
