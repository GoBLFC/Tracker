<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;
use App\Models\Kiosk;
use App\Models\TimeEntry;

class TimeEntryPolicy {
	/**
	 * Determine whether the user can view any models.
	 */
	public function viewAny(User $user, ?User $target = null): bool {
		return $user->id === $target?->id || $user->isManager();
	}

	/**
	 * Determine whether the user can view the model.
	 */
	public function view(User $user, TimeEntry $timeEntry): bool {
		return $user->id === $timeEntry->user_id || $user->isManager();
	}

	/**
	 * Determine whether the user can create models.
	 */
	public function create(User $creator, User $target, Event $event = null): bool {
		$validEvent = !$event || $event->isActive();
		return ($creator->id === $target->id && $validEvent && Kiosk::isSessionAuthorized())
			|| ($creator->isManager() && $validEvent)
			|| $creator->isAdmin();
	}

	/**
	 * Determine whether the user can update the model.
	 */
	public function update(User $user, TimeEntry $timeEntry): bool {
		$validEvent = $timeEntry->isForActiveEvent();
		return ($user->id === $timeEntry->user_id && $validEvent && Kiosk::isSessionAuthorized())
			|| ($user->isManager() && $validEvent)
			|| $user->isAdmin();
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(User $user, TimeEntry $timeEntry): bool {
		return ($user->isManager() && $timeEntry->isForActiveEvent()) || $user->isAdmin();
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(User $user, TimeEntry $timeEntry): bool {
		return $user->isAdmin();
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(User $user, TimeEntry $timeEntry): bool {
		return $user->isAdmin();
	}
}
