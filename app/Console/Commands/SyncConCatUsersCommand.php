<?php

namespace App\Console\Commands;

use App\Facades\ConCat;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncConCatUsersCommand extends Command implements Isolatable {
	/**
	 * The name and signature of the console command.
	 */
	protected $signature = 'auth:sync-concat
		{--apply : Save the changes (without this, the changes are only previewed)}';

	/**
	 * The console command description.
	 */
	protected $description = 'Syncs users with ConCat volunteers: creates missing volunteers, refreshes usernames and badge names, promotes staff, and demotes users who are no longer volunteers.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int {
		$apply = $this->option('apply');
		if (!$apply) $this->warn('Dry run: no changes will be saved. Pass --apply to save them.');

		$this->info('Authorizing with ConCat...');
		ConCat::authorize();

		$this->info('Fetching volunteers...');
		$volunteers = collect(ConCat::searchVolunteers())
			->filter(fn ($volunteer) => isset($volunteer->user->id))
			->keyBy(fn ($volunteer) => (int) $volunteer->user->id);

		// An empty list would demote every volunteer and staff member, so treat it as an error instead
		if ($volunteers->isEmpty()) {
			$this->error('ConCat returned no volunteers. Nothing was changed.');
			return self::FAILURE;
		}
		$this->info("Found {$volunteers->count()} " . Str::plural('volunteer', $volunteers->count()) . ' in ConCat.');

		// Badge names are only available on registrations, and ConCat requires the user IDs as strings
		$this->info('Fetching registrations for badge names...');
		$userIds = $volunteers->keys()->map(fn ($id) => (string) $id)->all();
		$registrations = collect(ConCat::searchRegistrationsByUserIds($userIds))
			->filter(fn ($registration) => isset($registration->user->id))
			->keyBy(fn ($registration) => (int) $registration->user->id);

		$plan = $this->buildPlan($volunteers, $registrations);
		$this->printPlan($plan);

		if ($apply) {
			DB::transaction(function () use ($plan) {
				foreach ([...$plan['create'], ...$plan['update'], ...$plan['demote']] as $user) $user->save();
			});

			$this->newLine();
			$this->info("Saved {$plan['create']->count()} new, {$plan['update']->count()} updated, and {$plan['demote']->count()} demoted users.");
		}

		return self::SUCCESS;
	}

	/**
	 * Works out the changes to make, filling in the user models without saving them
	 *
	 * @param Collection<int, \stdClass> $volunteers ConCat volunteers keyed by user ID
	 * @param Collection<int, \stdClass> $registrations ConCat registrations keyed by user ID
	 * @return array{create: Collection<int, User>, update: Collection<int, User>, demote: Collection<int, User>, skipped: Collection<int, User>}
	 */
	private function buildPlan(Collection $volunteers, Collection $registrations): array {
		$plan = ['create' => collect(), 'update' => collect(), 'demote' => collect(), 'skipped' => collect()];

		$users = User::withTrashed()
			->whereIn('badge_id', $volunteers->keys())
			->get()
			->keyBy('badge_id');

		foreach ($volunteers as $badgeId => $volunteer) {
			$user = $users[$badgeId] ?? null;

			// Don't bring back users that were deleted on purpose
			if ($user?->trashed()) {
				$plan['skipped']->push($user);
				continue;
			}

			if (!$user) {
				$user = new User(['badge_id' => $badgeId, 'role' => Role::Volunteer]);
				$this->fillUser($user, $volunteer->user, $registrations[$badgeId] ?? null);

				// Real names are PII, so new users get the same placeholder name used elsewhere
				$user->first_name = 'Unidentified';
				$user->last_name = Str::title($user->role->name);
				$plan['create']->push($user);
				continue;
			}

			$this->fillUser($user, $volunteer->user, $registrations[$badgeId] ?? null);
			if ($user->isDirty()) $plan['update']->push($user);
		}

		// Volunteers and staff that ConCat doesn't list as volunteers anymore are demoted to Attendee, keeping their
		// names. Logging in with ConCat promotes an Attendee back to Volunteer.
		$plan['demote'] = User::whereIn('role', [Role::Volunteer, Role::Staff])
			->whereNotIn('badge_id', $volunteers->keys())
			->orderBy('badge_id')
			->get()
			->each(fn ($user) => $user->role = Role::Attendee);

		return $plan;
	}

	/**
	 * Fills a user's username and badge name from ConCat and raises their role if needed. Real names are PII, so they
	 * are never copied. A role is only ever raised from Attendee or Volunteer, so Banned, Lead, Manager, and Admin are
	 * never touched.
	 */
	private function fillUser(User $user, \stdClass $conCatUser, ?\stdClass $registration): void {
		$user->username = $conCatUser->username;
		if ($registration?->badgeName) $user->badge_name = $registration->badgeName;

		$targetRole = ($conCatUser->isStaff ?? false) ? Role::Staff : Role::Volunteer;
		if (in_array($user->role, [Role::Attendee, Role::Volunteer], true) && $user->role->value < $targetRole->value) {
			$user->role = $targetRole;
		}
	}

	/**
	 * Prints the planned changes
	 */
	private function printPlan(array $plan): void {
		$this->newLine();
		$this->info("New users ({$plan['create']->count()}):");
		if ($plan['create']->isNotEmpty()) {
			$this->table(
				['Badge ID', 'Username', 'Badge Name', 'Role'],
				$plan['create']->map(fn ($user) => [$user->badge_id, $user->username, $user->badge_name, $user->role->name]),
			);
		}

		$this->newLine();
		$this->info("Updated users ({$plan['update']->count()}):");
		if ($plan['update']->isNotEmpty()) {
			$rows = [];
			foreach ($plan['update'] as $user) {
				foreach (array_keys($user->getDirty()) as $field) {
					$rows[] = [
						$user->badge_id,
						$user->getOriginal('username'),
						$field,
						$this->displayValue($user->getOriginal($field)),
						$this->displayValue($user->$field),
					];
				}
			}
			$this->table(['Badge ID', 'Username', 'Field', 'Old', 'New'], $rows);
		}

		$this->newLine();
		$this->info("Users to demote to Attendee, not found in ConCat volunteers ({$plan['demote']->count()}):");
		if ($plan['demote']->isNotEmpty()) {
			$this->table(
				['Badge ID', 'Username', 'Badge Name', 'Current Role'],
				$plan['demote']->map(fn ($user) => [
					$user->badge_id,
					$user->username,
					$user->badge_name,
					$user->getOriginal('role')->name,
				]),
			);
		}

		if ($plan['skipped']->isNotEmpty()) {
			$this->newLine();
			$this->warn("Skipped deleted users ({$plan['skipped']->count()}):");
			$this->table(
				['Badge ID', 'Username', 'Deleted At'],
				$plan['skipped']->map(fn ($user) => [$user->badge_id, $user->username, $user->deleted_at]),
			);
		}
	}

	/**
	 * Converts a value into something printable in a table
	 */
	private function displayValue(mixed $value): string {
		return $value instanceof Role ? $value->name : (string) $value;
	}
}
