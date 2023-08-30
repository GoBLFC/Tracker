<?php

namespace App\Http\Requests;

use App\Models\Reward;
use Illuminate\Foundation\Http\FormRequest;

class RewardStoreRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('create', Reward::class);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		return [
			'name' => 'required|string|max:64',
			'description' => 'required|string|max:1000',
			'hours' => 'required|integer|min:0|max:168',
		];
	}
}
