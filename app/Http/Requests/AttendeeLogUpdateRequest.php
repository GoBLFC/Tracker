<?php

namespace App\Http\Requests;

use App\Models\AttendeeLog;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendeeLogUpdateRequest extends FormRequest {
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
			'name' => [
				$this->isMethod('PUT') ? 'required' : 'sometimes',
				'string',
				'max:64',
				Rule::unique(AttendeeLog::class)
					->where(fn (Builder $query) => $query->where('event_id', $this->route('attendeeLog')->event_id))
					->ignore($this->route('attendeeLog'))
					->withoutTrashed(),
			],
		];
	}
}
