<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeEntryStoreRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('create', [TimeEntry::class, $this->route('user')])
			&& $this->user()->isManager();
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
			'start' => 'sometimes|nullable|required_with:stop|date',
			'stop' => "sometimes|nullable|date|after:{$start}",
			'notes' => 'sometimes|nullable|string|max:255',
		];
	}
}
