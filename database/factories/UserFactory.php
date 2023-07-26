<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory {
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array {
		return [
			'id' => fake()->randomNumber(8, true),
			'username' => fake()->userName(),
			'first_name' => fake()->firstName(),
			'last_name' => fake()->lastName(),
			'badge_name' => fake()->name(),
			'role' => 0,
			'tg_setup_code' => Str::random(32),
			'tg_uid' => fake()->randomNumber(8, true),
		];
	}

	/**
	 * Indicate that the model's telegram user ID should be null
	 */
	public function telegramUnlinked(): static {
		return $this->state(fn () => [
			'tg_uid' => null,
		]);
	}
}
