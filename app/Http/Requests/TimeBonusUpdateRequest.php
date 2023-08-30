<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeBonusUpdateRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('update', $this->route('bonus'));
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		$start = $this->input('start');
		$afterStartRule = $start ? "|after:{$start}" : '';
		return [
			'start' => 'sometimes|date',
			'stop' => "sometimes|date{$afterStartRule}",
			'modifier' => 'sometimes|decimal:0,2|min:1|max:10',
			'department_id' => 'sometimes|uuid|exists:App\Models\Department,id',
		];
	}
}
