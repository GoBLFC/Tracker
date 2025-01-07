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
			'badge_id' => fake()->randomNumber(8, true),
			'username' => fake()->userName(),
			'first_name' => fake()->firstName(),
			'last_name' => fake()->lastName(),
			'badge_name' => fake()->name(),
			'role' => 0,
			'tg_chat_id' => fake()->boolean(33) ? fake()->randomNumber(8, true) : null,
		];
	}

	/**
	 * Indicate that the model's role should be -1 and telegram user ID should be null
	 */
	public function attendee(): static {
		return $this->state(fn () => [
			'role' => -1,
			'tg_chat_id' => null,
		]);
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
