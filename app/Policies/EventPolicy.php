<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy {
	/**
	 * Determine whether the user can view any models.
	 */
	public function viewAny(User $user): bool {
		return $user->isManager();
	}

	/**
	 * Determine whether the user can view the model.
	 */
	public function view(User $user, Event $event): bool {
		return true;
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
	public function update(User $user, Event $event): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(User $user, Event $event): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(User $user, Event $event): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(User $user, Event $event): bool {
		return $user->isAdmin();
	}
}
