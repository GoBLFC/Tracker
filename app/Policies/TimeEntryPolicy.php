<?php

namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy {
	/**
	 * Determine whether the user can view any models.
	 */
	public function viewAny(User $user): bool {
		return $user->isManager();
	}

	/**
	 * Determine whether the user can view the model.
	 */
	public function view(User $user, TimeEntry $timeEntry): bool {
		return $user->id === $timeEntry->user_id;
	}

	/**
	 * Determine whether the user can create models.
	 */
	public function create(User $creator, User $target): bool {
		return $creator->id === $target->id || $creator->isManager();
	}

	/**
	 * Determine whether the user can update the model.
	 */
	public function update(User $user, TimeEntry $timeEntry): bool {
		return $user->id === $timeEntry->user_id || $user->isManager();
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(User $user, TimeEntry $timeEntry): bool {
		return $user->isManager();
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(User $user, TimeEntry $timeEntry): bool {
		return $user->isManager();
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(User $user, TimeEntry $timeEntry): bool {
		return $user->isAdmin();
	}
}
