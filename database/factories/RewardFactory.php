<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class RewardFactory extends Factory {
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array {
		return [
			'name' => Str::title(fake()->words(3, true)),
			'description' => fake()->paragraphs(3, true),
			'hours' => fake()->numberBetween(4, 48),
			'event_id' => \App\Models\Event::factory(),
		];
	}
}
