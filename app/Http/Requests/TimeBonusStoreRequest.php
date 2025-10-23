<?php

namespace App\Http\Requests;

use App\Models\TimeBonus;
use Illuminate\Foundation\Http\FormRequest;

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
			'departments' => 'required|array|min:1|exists:App\Models\Department,id',
		];
	}
}
