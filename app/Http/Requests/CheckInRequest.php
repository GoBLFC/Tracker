<?php

namespace App\Http\Requests;

use App\Models\TimeEntry;
use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('create', [TimeEntry::class, $this->user()]);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		return [
			'department_id' => 'required|uuid|exists:App\Models\Department,id',
		];
	}
}
