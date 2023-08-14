<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
			'external_id' => fake()->randomNumber(8, true),
			'username' => fake()->userName(),
			'first_name' => fake()->firstName(),
			'last_name' => fake()->lastName(),
			'badge_name' => fake()->name(),
			'role' => 0,
			'tg_chat_id' => fake()->randomNumber(8, true),
		];
	}

	/**
	 * Indicate that the model's telegram user ID should be null
	 */
	public function telegramUnlinked(): static {
		return $this->state(fn () => [
			'tg_chat_id' => null,
		]);
	}
}
