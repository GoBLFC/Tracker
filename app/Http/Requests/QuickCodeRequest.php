<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class QuickCodeRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		return [
			'code' => 'required|string|alpha_num|size:4',
		];
	}

	/**
	 * Handle a passed validation attempt.
	 */
	protected function passedValidation(): void {
		$this->replace(['code' => Str::upper($this->code)]);
	}
}
