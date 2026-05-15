<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class DepartmentFactory extends Factory {
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array {
		return [
			'name' => Str::limit(fake()->jobTitle(), 61),
			'hidden' => fake()->boolean(20),
			'event_id' => \App\Models\Event::factory(),
		];
	}

	/**
	 * Indicate that the model's hidden flag should not be set
	 */
	public function visible(): static {
		return $this->state(fn () => [
			'hidden' => false,
		]);
	}

	/**
	 * Indicate that the model's hidden flag should be set
	 */
	public function hidden(): static {
		return $this->state(fn () => [
			'hidden' => true,
		]);
	}
}
