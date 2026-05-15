<?php

namespace App\Http\Requests;

use App\Models\Reward;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RewardUpdateRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('update', $this->route('reward'));
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		$requiredOrSometimes = $this->isMethod('PUT') ? 'required' : 'sometimes';
		return [
			'name' => [
				$requiredOrSometimes,
				'string',
				'max:64',
				Rule::unique(Reward::class)
					->where(fn (Builder $query) => $query->where('event_id', $this->route('reward')->event_id))
					->ignore($this->route('reward'))
					->withoutTrashed(),
			],
			'name' => "{$requiredOrSometimes}|string|max:64",
			'description' => "{$requiredOrSometimes}|string|max:1000",
			'hours' => "{$requiredOrSometimes}|integer|min:0|max:168",
		];
	}
}
