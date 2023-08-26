<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class TimeEntryCreateRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->isManager();
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		$start = $this->input('start');
		return [
			'event_id' => 'sometimes|nullable|uuid|exists:App\Models\Event,id',
			'department_id' => 'required|uuid|exists:App\Models\Department,id',
			'start' => 'sometimes|nullable|required_with:stop|date|before_or_equal:now',
			'stop' => "sometimes|nullable|date|after:{$start}",
			'notes' => 'sometimes|nullable|string|max:255',
		];
	}
}
