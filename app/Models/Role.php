<?php

namespace App\Models;

use Illuminate\Support\Str;
use Throwable;

enum Role: int {
	case Admin = 4;
	case Manager = 3;
	case Lead = 2;
	case Staff = 1;
	case Volunteer = 0;
	case Attendee = -1;
	case Banned = -2;

	/**
	 * Get the description for the case
	 */
	public function description(): string {
		return self::descriptions[$this->value];
	}

	/**
	 * Get the Bootstrap color class (like "danger" or "warning") for the case
	 */
	public function colorClass(): string {
		return self::colorClasses[$this->value];
	}

	/**
	 * Get the action label (like "Make Admin" or "BAN") for the case
	 */
	public function actionLabel(): string {
		return self::actionLabels[$this->value];
	}

	/**
	 * Array of case values to descriptions for the corresponding role
	 */
	public const descriptions = [
		4 => 'Administrators can do anything.',
		3 => 'Managers can view, edit, and create time entries on behalf of other users, as well as claim rewards, create new users with a badge ID, manage attendees and gatekeepers in attendee logs, authorize kiosks, and bypass the lockdown.',
		2 => 'Leads can authorize kiosks.',
		1 => 'Staff can bypass the need for kiosks.',
		0 => 'Volunteers can only check in and out for shifts.',
		-1 => 'Attendees are only a placeholder for users added to attendee logs.',
		-2 => 'Banned users can\'t do anything.',
	];

	/**
	 * Array of case values to action labels for the corresponding role
	 */
	public const actionLabels = [
		4 => 'Make Admin',
		3 => 'Make Manager',
		2 => 'Make Lead',
		1 => 'Make Staff',
		0 => 'Make Volunteer',
		-1 => 'Make Attendee',
		-2 => 'BAN',
	];

	/**
	 * Array of case values to color classes for the corresponding role
	 */
	public const colorClasses = [
		4 => 'danger',
		3 => 'warning',
		2 => 'success',
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
