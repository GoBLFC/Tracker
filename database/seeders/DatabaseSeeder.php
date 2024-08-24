<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
	/**
	 * Seed the application's database.
	 */
	public function run(): void {
		// Create some departments
		$departments = \App\Models\Department::factory(10)->create();
		\App\Models\Department::factory(4)->hidden()->create();

		// Create some events
		$events = \App\Models\Event::factory(5)
			->hasRewards(4)
			->has(
				\App\Models\TimeBonus::factory(5)
					->hasAttached($departments)
			)
			->create();

		// Create a bunch of dummy users with some content
		\App\Models\User::factory(30)->telegramUnlinked()->has(
			\App\Models\TimeEntry::factory(20)
				->recycle($events)
				->recycle($departments)
		)->create();
	}
}
