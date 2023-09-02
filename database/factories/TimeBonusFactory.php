<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class TimeBonusFactory extends Factory {
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array {
		$start = new Carbon(fake()->dateTimeInInterval('-7 days', '+5 days'));
		$stop = $start->avoidMutation()->addHours(fake()->numberBetween(1, 8));

		return [
			'start' => $start,
			'stop' => $stop,
			'modifier' => fake()->randomElement([1.25, 1.5, 2, 3, 4]),
			'event_id' => \App\Models\Event::factory(),
		];
	}
}
