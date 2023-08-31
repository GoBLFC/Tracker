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
	 * Get the description for the case
	 */
	public function description(): string {
		return static::descriptions[$this->value];
	}

	/**
	 * Get the Bootstrap color class (like "danger" or "warning") for the case
	 */
	public function colorClass(): string {
		return static::colorClasses[$this->value];
	}

	/**
	 * Get the action label (like "Make Admin" or "BAN") for the case
	 */
	public function actionLabel(): string {
		return static::actionLabels[$this->value];
	}

	/**
	 * Array of case values to descriptions for the corresponding role
	 */
	public const descriptions = [
		3 => 'Administrators can do anything.',
		2 => 'Managers can view, edit, and create time entries on behalf of other users, as well as create new users with a badge ID, authorize kiosks, and bypass the lockdown.',
		1 => 'Leads can authorize kiosks.',
		0 => 'Volunteers can only check in and out for shifts.',
		-1 => 'Banned users can\'t do anything.',
	];

	/**
	 * Array of case values to action labels for the corresponding role
	 */
	public const actionLabels = [
		3 => 'Make Admin',
		2 => 'Make Manager',
		1 => 'Make Lead',
		0 => 'Make Volunteer',
		-1 => 'BAN',
	];

	/**
	 * Array of case values to color classes for the corresponding role
	 */
	public const colorClasses = [
		3 => 'danger',
		2 => 'warning',
		1 => 'success',
		0 => 'info',
		-1 => 'danger',
	];

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
