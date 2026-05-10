<?php

namespace Database\Seeders;

use App\Models\Activity;
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
		// Create some users
		$users = User::factory(100)
			->telegramUnlinked()
			->create();

		// Create some events with departments, rewards, time bonuses, and time entries
		for ($i = 0; $i < 5; $i++) {
			$event = Event::factory()
				->hasRewards(4)
				->create();

			$departments = Department::factory(15)
				->for($event)
				->create();

			TimeBonus::factory(5)
				->for($event)
				->recycle($departments)
				->create();

			TimeEntry::factory(1000)
				->for($event)
				->recycle($departments)
				->recycle($users)
				->create();
		}

		// Update all time entry activities' creation time to be appropriate
		$timeActivities = Activity::with('subject')->whereSubjectType(TimeEntry::class)->lazy();
		foreach ($timeActivities as $activity) {
			$activity->created_at = $activity->subject->stop ?? $activity->subject->start;
			$activity->save();
		}
	}
}
