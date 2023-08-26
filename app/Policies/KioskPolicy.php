<?php

namespace App\Policies;

use App\Models\Kiosk;
use App\Models\User;

class KioskPolicy {
	/**
	 * Determine whether the user can view any models.
	 */
	public function viewAny(User $user): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can view the model.
	 */
	public function view(User $user, Kiosk $kiosk): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can create models.
	 */
	public function create(User $user): bool {
		return $user->isLead();
	}

	/**
	 * Determine whether the user can update the model.
	 */
	public function update(User $user, Kiosk $kiosk): bool {
		return false;
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(User $user, Kiosk $kiosk): bool {
		return $user->isLead();
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(User $user, Kiosk $kiosk): bool {
		return $user->isLead();
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(User $user, Kiosk $kiosk): bool {
		return $user->isLead();
	}
}
