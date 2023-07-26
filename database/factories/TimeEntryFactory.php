<?php

namespace Database\Factories;

use DateInterval;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class TimeEntryFactory extends Factory {
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array {
		$start = fake()->dateTimeThisYear();
		$user = \App\Models\User::factory();

		return [
			'user_id' => $user,
			'start' => $start,
			'stop' => (clone $start)->add(new DateInterval('PT2H')),
			'department_id' => \App\Models\Department::factory(),
			'notes' => fake()->paragraph(),
			'creator_user_id' => $user,
			'auto' => false,
			'event_id' => \App\Models\Event::factory(),
		];
	}

	/**
	 * Indicate that the model's stop timestamp should be null
	 */
	public function inProgress(): static {
		return $this->state(fn () => [
			'stop' => null,
		]);
	}

	/**
	 * Indicate that the model's auto flag should be set
	 */
	public function autoStopped(): static {
		return $this->state(fn () => [
			'auto' => true,
		]);
	}
}
