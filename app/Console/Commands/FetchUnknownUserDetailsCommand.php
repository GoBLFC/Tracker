<?php

namespace App\Console\Commands;

use App\Facades\ConCat;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Str;

class FetchUnknownUserDetailsCommand extends Command implements Isolatable {
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
		$this->info('Fetching registration information for all of them...');
		$badgeIds = $unknownUsers->pluck('badge_id')
			->map(fn ($id) => (string) $id)
			->toArray();
		$registrations = collect(ConCat::searchRegistrationsByUserIds($badgeIds))->keyBy('user.id');

		// Handle each user individually
		$updatedUsers = 0;
		foreach ($unknownUsers as $user) {
			$updated = $this->updateUser($user, $registrations[(string) $user->badge_id] ?? null);
			if ($updated) $updatedUsers++;
		}

		$userWord = Str::plural('user', $updatedUsers);
		$this->info("Populated {$updatedUsers} unknown {$userWord}.");
	}

	/**
	 * Updates a user using information from a ConCat registration. If a registration isn't available, the ConCat user
	 * entity is retrieved to get the basic information necessary for them.
	 */
	private function updateUser(User $user, ?\stdClass $registration): bool {
		// Retrieve & fill with the ConCat user entity if we don't already have it from the registration
		if (!$registration?->user) {
			$this->info("Fetching user information for {$user->audit_name}...");
			try {
				$conCatUser = ConCat::getUser($user->badge_id);
				$user->fillFromConCatUser($conCatUser);
			} catch (\Throwable $_err) {
				if ($registration) {
					$this->warn("Failed to retrieve user information for {$user->audit_name}. Proceeding with partial registration information.");
				} else {
					$this->warn("Failed to retrieve user information for {$user->audit_name}, and no registration information was found. Skipping.");
					return false;
				}
			}
		}

		// Update the user
		if ($registration) $user->fillFromConCatRegistration($registration);
		$user->save();
		$this->info("Updated {$user->audit_name}.");
		return true;
	}
}
