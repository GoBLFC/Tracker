<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\TimeBonus;
use App\Models\User;

class TimeBonusPolicy {
	/**
	 * Determine whether the user can view any models.
	 */
	public function viewAny(User $user): bool {
		return $user->isManager();
	}

	/**
	 * Determine whether the user can view the model.
	 */
	public function view(User $user, TimeBonus $timeBonus): bool {
		return $user->isManager() || $timeBonus->isForActiveEvent();
	}

	/**
	 * Determine whether the user can view some models that belong to an event.
	 */
	public function viewForEvent(User $user, ?Event $event): bool {
		return $user->isManager() || $event?->isActive();
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
	public function update(User $user, TimeBonus $timeBonus): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(User $user, TimeBonus $timeBonus): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(User $user, TimeBonus $timeBonus): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(User $user, TimeBonus $timeBonus): bool {
		return $user->isAdmin();
	}
}
