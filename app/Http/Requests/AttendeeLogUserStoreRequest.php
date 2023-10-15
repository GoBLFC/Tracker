<?php

namespace App\Http\Requests;

use App\Models\AttendeeLog;
use Illuminate\Foundation\Http\FormRequest;

class AttendeeLogUserStoreRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('update', $this->route('attendeeLog'));
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
