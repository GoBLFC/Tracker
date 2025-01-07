<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
		$start = new Carbon(fake()->dateTimeInInterval('-7 days', '+5 days'));
		$stop = $start->avoidMutation()
			->addHours(fake()->numberBetween(1, 12))
			->addMinutes(fake()->numberBetween(0, 59))
			->addSeconds(fake()->numberBetween(0, 59));

		return [
			'user_id' => \App\Models\User::factory(),
			'start' => $start,
			'stop' => $stop,
			'department_id' => \App\Models\Department::factory(),
			'notes' => fake()->boolean(25) ? fake()->paragraph() : null,
			'creator_user_id' => fn (array $attributes) => $attributes['user_id'],
			'auto' => fake()->boolean(5),
			'event_id' => \App\Models\Event::factory(),
			'created_at' => $start,
			'updated_at' => $stop,
		];
	}

	/**
	 * Indicate that the model's stop timestamp should be null
	 */
	public function ongoing(): static {
		return $this->state(fn ($state) => [
			'stop' => null,
			'created_at' => $state['start'],
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

	/**
	 * Indicate that the model's notes should be null
	 */
	public function withoutNotes(): static {
		return $this->state(fn () => [
			'notes' => null,
		]);
	}
}
