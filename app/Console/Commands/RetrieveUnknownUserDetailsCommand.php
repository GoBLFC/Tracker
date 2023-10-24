<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Facades\ConCat;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class RetrieveUnknownUserDetailsCommand extends Command implements Isolatable {
	/**
	 * The name and signature of the console command.
	 */
	protected $signature = 'auth:fetch-unknown';

	/**
	 * The console command description.
	 */
	protected $description = 'Retrieves user details for all unknown users from ConCat.';

	/**
	 * Execute the console command.
	 */
	public function handle(): void {
		// Get all of the unknown users and make sure there are some
		$unknownUsers = User::whereUsername('unknown')->get();
		if ($unknownUsers->isEmpty()) {
			$this->info('No unknown users to retrieve details for.');
			return;
		}

		$areOrIs = $unknownUsers->count() > 1 ? 'are' : 'is';
		$userWord = Str::plural('user', $unknownUsers->count());
		$this->info("There {$areOrIs} {$unknownUsers->count()} unknown {$userWord} to look up.");

		// Authorize with ConCat
		$this->info('Authorizing with ConCat...');
		ConCat::authorize();

		// Retrieve all registrations for the users
		$this->info("Fetching registration information for all of them...");
		$badgeIds = $unknownUsers->pluck('badge_id')
			->map(fn ($id) => (string) $id)
			->toArray();
		$registrations = collect(ConCat::searchRegistrationsByUserIds($badgeIds))->keyBy('user.id');

		// Handle each user individually
		$updatedUsers = 0;
		foreach ($unknownUsers as $user) {
			$updated = $this->updateUser($user, $registrations[(string) $user->badge_id]);
			if ($updated) $updatedUsers++;
		}

		$userWord = Str::plural('user', $updatedUsers);
		$this->info("Populated {$updatedUsers} unknown {$userWord}.");
	}

	/**
	 * Retrieves a volunteer entity for a user and updates it with information from that and a registration entity
	 */
	private function updateUser(User $user, ?\stdClass $registration): bool {
		// Fetch the volunteer information for the user.
		// Sadly, ConCat does not currently allow  bulk-retrieval of volunteer entities.
		$this->info("Fetching volunteer information for {$user->audit_name}...");
		try {
			$volunteer = ConCat::getVolunteer($user->badge_id);
		} catch (\Throwable $_err) {
			$volunteer = null;
			$this->warn("Failed to retrieve volunteer information for {$user->audit_name}.");

			// If a registration wasn't provided either, then there's nothing to do for this user
			if (!$registration) {
				$this->warn("{$user->audit_name} doesn't have volunteer or registration information available. Skipping.");
				return false;
			}
		}

		// Update the user
		$user->username = $volunteer?->user?->username ?? $registration?->user?->username ?? $user->username;
		$user->first_name = $volunteer?->user?->firstName ?? $registration?->user?->firstName ?? $user->first_name;
		$user->last_name = $volunteer?->user?->lastName ?? $registration?->user?->lastName ?? $user->last_name;
		$user->badge_name = $registration?->badgeName ?? $user->badge_name;
		$user->save();
		$this->info("Updated {$user->audit_name}.");
		return true;
	}
}
