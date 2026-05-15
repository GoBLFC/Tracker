<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\TimeEntry;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class CheckInRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool|RedirectResponse|JsonResponse {
		return $this->user()->can('create', [TimeEntry::class, $this->user(), $this->route('event')]);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		return [
			'department_id' => [
				'required',
				'uuid',
				Rule::exists(Department::class, 'id')
					->where(fn (Builder $query) => $query->where('event_id', $this->route('event')->id))
					->withoutTrashed(),
			],
		];
	}
}
