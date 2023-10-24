<?php

namespace App\Models;

use Illuminate\Support\Str;
use Throwable;

enum Role: int {
	case Admin = 3;
	case Manager = 2;
	case Lead = 1;
	case Volunteer = 0;
	case Attendee = -1;
	case Banned = -2;

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
		2 => 'Managers can view, edit, and create time entries on behalf of other users, as well as claim rewards, create new users with a badge ID, manage attendees and gatekeepers in attendee logs, authorize kiosks, and bypass the lockdown.',
		1 => 'Leads can authorize kiosks.',
		0 => 'Volunteers can only check in and out for shifts.',
		-1 => 'Attendees are only a placeholder for users added to attendee logs.',
		-2 => 'Banned users can\'t do anything.',
	];

	/**
	 * Array of case values to action labels for the corresponding role
	 */
	public const actionLabels = [
		3 => 'Make Admin',
		2 => 'Make Manager',
		1 => 'Make Lead',
		0 => 'Make Volunteer',
		-1 => 'Make Attendee',
		-2 => 'BAN',
	];

	/**
	 * Array of case values to color classes for the corresponding role
	 */
	public const colorClasses = [
		3 => 'danger',
		2 => 'warning',
		1 => 'success',
		0 => 'info',
		-1 => 'secondary',
		-2 => 'danger',
	];

	/**
	 * Get the enumeration case with the specified name
	 */
	public static function fromName(string $roleName): static {
		$roleName = Str::title($roleName);

		try {
			$role = constant("static::{$roleName}");
		} catch (Throwable $err) {
			throw new \ValueError("Unknown role name: {$roleName}", previous: $err);
		}

		return $role;
	}
}
