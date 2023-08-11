<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
	/**
	 * Seed the application's database.
	 */
	public function run(): void {
		// Create some events
		$events = \App\Models\Event::factory(5)
			->hasRewards(4)
			->create();

		// Create some departments
		$departments = \App\Models\Department::factory(10)->create();
		\App\Models\Department::factory(4)->hidden()->create();

		// Create a bunch of dummy users with some content
		\App\Models\User::factory(30)->has(
			\App\Models\TimeEntry::factory(5)
				->recycle($events)
				->recycle($departments)
		)->create();

		// Create an admin user for Gawdl3y with some fake info that will be overwritten upon auth
		\App\Models\User::factory()->telegramUnlinked()->create([
			'id' => 19,
			'username' => 'gawdl3y',
			'first_name' => 'Bob',
			'last_name' => 'Malooga',
			'role' => \App\Models\Role::Admin,
		]);
	}
}
