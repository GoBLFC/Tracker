<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class SetUserRoleCommand extends Command implements PromptsForMissingInput {
	/**
	 * The name and signature of the console command.
	 */
	protected $signature = 'auth:set-role {user : The user to set the role of} {role : The role to set the user to}';

	/**
	 * The console command description.
	 */
	protected $description = 'Sets the role of a user.';

	/**
	 * Execute the console command.
	 */
	public function handle(): void {
		$user = $this->argument('user');
		$user = is_numeric($user) ? User::whereBadgeId($user)->firstOrFail() : User::findOrFail($user);

		$role = $this->argument('role');
		$role = Role::fromName($role);

		$oldRole = $user->role;
		$user->role = $role;
		$user->save();

		$this->info("Changed the role of {$user->audit_name} from {$oldRole->name} to {$role->name}.");
	}

	protected function promptForMissingArgumentsUsing() {
		return [
			'user' => fn () => search(
				label: 'Search for a user:',
				placeholder: 'Badge ID, UUID, username, partial name, etc...',
				options: function ($value) {
					if (strlen($value) === 0) return [];
					if (Str::isUuid($value)) return User::whereId($value)->pluck('username', 'id')->all();
					if (is_numeric($value)) return User::whereBadgeId($value)->pluck('username', 'id')->all();

					$wildSearch = '%' . strtolower($value) . '%';
					return User::whereRaw('lower(username) like ?', $wildSearch)
						->orWhereRaw('lower(badge_name) like ?', $wildSearch)
						->orWhereRaw('lower(first_name) like ?', $wildSearch)
						->orWhereRaw('lower(last_name) like ?', $wildSearch)
						->pluck('username', 'badge_id')
						->all();
				},
			),
			'role' => fn () => select(
				label: 'Select a role:',
				options: collect(Role::cases())->pluck('name'),
				default: 'Volunteer',
			),
		];
	}
}
