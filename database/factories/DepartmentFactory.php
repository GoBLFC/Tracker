<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

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
			'name' => Str::limit(fake()->jobTitle(), 64),
			'hidden' => false,
		];
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
