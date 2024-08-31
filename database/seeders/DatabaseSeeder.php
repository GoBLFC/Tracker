<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Event;
use App\Models\TimeBonus;
use App\Models\TimeEntry;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
	/**
	 * Seed the application's database.
	 */
	public function run(): void {
		// Create some departments
		$departments = Department::factory(10)->create();
		Department::factory(4)->hidden()->create();

		// Create some events
		$events = Event::factory(5)
			->hasRewards(4)
			->has(
				TimeBonus::factory(5)
					->hasAttached($departments)
			)
			->create();

		// Create a bunch of dummy users with some content
		User::factory(30)->telegramUnlinked()->has(
			TimeEntry::factory(20)
				->recycle($events)
				->recycle($departments)
		)->create();
	}
}
