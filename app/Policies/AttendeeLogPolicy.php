<?php

namespace App\Policies;

use App\Models\AttendeeLog;
use App\Models\User;

class AttendeeLogPolicy {
	/**
	 * Determine whether the user can view any models.
	 */
	public function viewAny(User $user): bool {
		return $user->isManager();
	}

	/**
	 * Determine whether the user can view the model.
	 */
	public function view(User $user, AttendeeLog $attendeeLog): bool {
		return $user->isManager()
			|| ($attendeeLog->gatekeepers()->whereUserId($user->id)->exists() && $attendeeLog->event->isActive());
	}

	/**
	 * Determine whether the user can create models.
	 */
	public function create(User $user): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can update the model.
	 */
	public function update(User $user, AttendeeLog $attendeeLog): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(User $user, AttendeeLog $attendeeLog): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(User $user, AttendeeLog $attendeeLog): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(User $user, AttendeeLog $attendeeLog): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can manage gatekeepers on the log.
	 */
	public function manageGatekeepers(User $user, AttendeeLog $attendeeLog): bool {
		return ($user->isManager() && $attendeeLog->isForActiveEvent())
			|| $user->isAdmin();
	}

	/**
	 * Determine whether the user can manage attendees on the log.
	 */
	public function manageAttendees(User $user, AttendeeLog $attendeeLog): bool {
		$isActive = $attendeeLog->isForActiveEvent();
		return $user->isAdmin()
			|| ($user->isManager() && $isActive)
			|| ($attendeeLog->gatekeepers()->whereUserId($user->id)->exists() && $isActive);
	}
}
