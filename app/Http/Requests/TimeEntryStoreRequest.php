<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\TimeEntry;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TimeEntryStoreRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('create', [TimeEntry::class, $this->route('user'), $this->route('event')]);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		$start = $this->input('start');
		$startRestriction = !$this->has('stop') ? '|before:now' : '';
		return [
			'department_id' => [
				'required',
				'uuid',
				Rule::exists(Department::class, 'id')
					->where(fn (Builder $query) => $query->where('event_id', $this->route('event')->id))
					->withoutTrashed(),
			],
			'start' => "sometimes|nullable|required_with:stop|date{$startRestriction}",
			'stop' => "sometimes|nullable|date|after:{$start}",
			'notes' => 'sometimes|nullable|string|max:255',
		];
	}

	/**
	 * Handle a passed validation attempt.
	 */
	public function passedValidation(): void {
		$this->merge([
			'start' => $this->date('start')?->timezone(config('app.timezone')) ?? now(),
			'stop' => $this->date('stop')?->timezone(config('app.timezone')),
		]);
	}
}
