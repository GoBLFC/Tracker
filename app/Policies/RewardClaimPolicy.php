<?php

namespace App\Policies;

use App\Models\Reward;
use App\Models\RewardClaim;
use App\Models\User;

class RewardClaimPolicy {
	/**
	 * Determine whether the user can view any models.
	 */
	public function viewAny(User $user, ?User $target = null): bool {
		return $user->id === $target?->id || $user->isManager();
	}

	/**
	 * Determine whether the user can view the model.
	 */
	public function view(User $user, RewardClaim $rewardClaim): bool {
		return $user->isManager();
	}

	/**
	 * Determine whether the user can create models.
	 */
	public function create(User $user, ?Reward $reward = null): bool {
		return ($user->isManager() && (!$reward || $reward->isForActiveEvent())) || $user->isAdmin();
	}

	/**
	 * Determine whether the user can update the model.
	 */
	public function update(User $user, RewardClaim $rewardClaim): bool {
		return false;
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(User $user, RewardClaim $rewardClaim): bool {
		return ($user->isManager() && $rewardClaim->reward->isForActiveEvent()) || $user->isAdmin();
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(User $user, RewardClaim $rewardClaim): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(User $user, RewardClaim $rewardClaim): bool {
		return $user->isAdmin();
	}
}
