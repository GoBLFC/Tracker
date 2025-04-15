<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginateRequest extends FormRequest {
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		return [
			'page' => 'sometimes|nullable|integer',
			'count' => 'sometimes|nullable|integer|between:1,50',
		];
	}

	/**
	 * Handle a passed validation attempt.
	 */
	protected function passedValidation(): void {
		$this->mergeIfMissing([
			'page' => '0',
			'count' => '20',
		]);
	}
}
