<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendeeLogUserStoreRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		// Authorization is dependent on user input and is done in the controller
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		return [
			'badge_id' => 'required|integer|digits_between:1,8',
			'type' => 'sometimes|nullable|in:attendee,gatekeeper',
		];
	}
}
