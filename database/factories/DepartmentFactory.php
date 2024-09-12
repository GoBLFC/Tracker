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
			'name' => Str::limit(fake()->jobTitle(), 63),
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
