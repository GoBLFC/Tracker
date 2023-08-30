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
	 * Gets the Bootstrap color class (like "danger" or "warning") for the case
	 */
	public function colorClass(): string {
		return [
			3 => 'danger',
			2 => 'warning',
			1 => 'success',
			0 => 'info',
			-1 => 'danger',
		][$this->value];
	}

	/**
	 * Get the action label (like "Make Admin" or "BAN") for the case
	 */
	public function actionLabel(): string {
		return [
			3 => 'Make Admin',
			2 => 'Make Manager',
			1 => 'Make Lead',
			0 => 'Make Volunteer',
			-1 => 'BAN',
		][$this->value];
	}

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
