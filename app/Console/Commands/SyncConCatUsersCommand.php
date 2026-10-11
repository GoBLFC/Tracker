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
		{--apply : Write the changes to the database (without this, only a preview is shown)}';

	/**
	 * The console command description.
	 */
	protected $description = 'Syncs users with ConCat volunteers: refreshes usernames and badge names, creates missing volunteers, promotes staff, and demotes users who are no longer volunteers.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int {
		$apply = $this->option('apply');
		if (!$apply) $this->warn('Dry run: no changes will be saved. Pass --apply to save them.');

		// Pull all volunteers and their registrations (for badge names) from ConCat
		$this->info('Authorizing with ConCat...');
		ConCat::authorize();

		$this->info('Fetching volunteers...');
		$volunteers = collect(ConCat::searchVolunteers())
			->filter(fn ($volunteer) => isset($volunteer->user->id))
			->keyBy(fn ($volunteer) => (string) $volunteer->user->id);
		$this->info("Found {$volunteers->count()} " . Str::plural('volunteer', $volunteers->count()) . ' in ConCat.');

		$this->info('Fetching registrations for badge names...');
		// PHP turns numeric string keys into ints, but ConCat requires the user IDs as strings
		$registrations = collect(ConCat::searchRegistrationsByUserIds($volunteers->keys()->map(fn ($id) => (string) $id)->all()))
			->filter(fn ($registration) => isset($registration->user->id))
			->keyBy(fn ($registration) => (string) $registration->user->id);

		$users = User::withTrashed()
			->whereIn('badge_id', $volunteers->keys())
			->get()
			->keyBy(fn ($user) => (string) $user->badge_id);

		$plan = $this->buildPlan($volunteers, $registrations, $users);
		$this->printPlan($plan);

		if ($apply) {
			DB::transaction(function () use ($plan) {
				foreach ($plan['create'] as $user) $user->save();
				foreach ($plan['update'] as $change) $change['user']->save();
				foreach ($plan['demote'] as $user) {
					$user->role = Role::Attendee;
					$user->save();
				}
			});
			$this->info('Saved ' . count($plan['create']) . ' new, ' . count($plan['update']) . ' updated, and ' . $plan['demote']->count() . ' demoted ' . Str::plural('user', count($plan['create']) + count($plan['update']) + $plan['demote']->count()) . '.');
		}

		return self::SUCCESS;
	}

	/**
	 * Works out the changes to make without saving anything
	 *
	 * @return array{create: User[], update: array<array{user: User, changes: array<string, array{0: mixed, 1: mixed}>}>, skipped: User[], demote: Collection<int, User>}
	 */
	private function buildPlan(Collection $volunteers, Collection $registrations, Collection $users): array {
		$plan = ['create' => [], 'update' => [], 'skipped' => [], 'demote' => collect()];

		foreach ($volunteers as $badgeId => $volunteer) {
			$conCatUser = $volunteer->user;
			$registration = $registrations[$badgeId] ?? null;
			$user = $users[$badgeId] ?? null;

			// Don't bring back users that were deleted on purpose
			if ($user?->trashed()) {
				$plan['skipped'][] = $user;
				continue;
			}

			if (!$user) {
				$user = new User(['badge_id' => (int) $badgeId, 'role' => Role::Volunteer]);
				$this->fillUser($user, $conCatUser, $registration);

				// Real names are PII, so new users get the same placeholder name used elsewhere
				$user->first_name = 'Unidentified';
				$user->last_name = Str::title($user->role->name);
				$plan['create'][] = $user;
				continue;
			}

			$this->fillUser($user, $conCatUser, $registration);
			if ($user->isDirty()) {
				$changes = [];
				foreach (array_keys($user->getDirty()) as $field) {
					$changes[$field] = [$this->displayValue($user->getOriginal($field)), $this->displayValue($user->$field)];
				}
				$plan['update'][] = ['user' => $user, 'changes' => $changes];
			}
		}

		// Volunteer and Staff users that ConCat no longer lists as volunteers get demoted to Attendee. Names are left as-is.
		// Logging in with ConCat promotes an Attendee back to Volunteer.
		$plan['demote'] = User::whereIn('role', [Role::Volunteer, Role::Staff])
			->whereNotIn('badge_id', $volunteers->keys())
			->orderBy('badge_id')
			->get();

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
		$this->info('New users (' . count($plan['create']) . '):');
		if (count($plan['create']) > 0) {
			$this->table(
				['Badge ID', 'Username', 'Badge Name', 'Role'],
				collect($plan['create'])->map(fn ($user) => [
					$user->badge_id,
					$user->username,
					$user->badge_name,
					$user->role->name,
				]),
			);
		}

		$this->newLine();
		$this->info('Updated users (' . count($plan['update']) . '):');
		if (count($plan['update']) > 0) {
			$rows = [];
			foreach ($plan['update'] as $change) {
				foreach ($change['changes'] as $field => [$old, $new]) {
					$rows[] = [$change['user']->badge_id, $change['user']->getOriginal('username'), $field, $old, $new];
				}
			}
			$this->table(['Badge ID', 'Username', 'Field', 'Old', 'New'], $rows);
		}

		if (count($plan['skipped']) > 0) {
			$this->newLine();
			$this->warn('Skipped deleted users (' . count($plan['skipped']) . '):');
			$this->table(
				['Badge ID', 'Username', 'Deleted At'],
				collect($plan['skipped'])->map(fn ($user) => [$user->badge_id, $user->username, $user->deleted_at]),
			);
		}

		$this->newLine();
		$this->info("Demote to Attendee, not found in ConCat volunteers ({$plan['demote']->count()}):");
		if ($plan['demote']->isNotEmpty()) {
			$this->table(
				['Badge ID', 'Username', 'Badge Name', 'Current Role'],
				$plan['demote']->map(fn ($user) => [$user->badge_id, $user->username, $user->badge_name, $user->role->name]),
			);
		}
	}

	/**
	 * Converts a value into something printable in a table
	 */
	private function displayValue(mixed $value): string {
		if ($value instanceof Role) return $value->name;
		return (string) $value;
	}
}
