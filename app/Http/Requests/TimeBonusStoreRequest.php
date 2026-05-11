<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\TimeBonus;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TimeBonusStoreRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('create', TimeBonus::class);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		$start = $this->input('start');
		return [
			'start' => 'required|date',
			'stop' => "required|date|after:{$start}",
			'modifier' => 'required|decimal:0,2|min:1|max:10',
			'departments' => [
				'required',
				'list',
				'min:1',
				Rule::exists(Department::class, 'id')
					->where(fn (Builder $query) => $query->where('event_id', $this->route('event')->id))
					->withoutTrashed(),
			],
		];
	}
}
