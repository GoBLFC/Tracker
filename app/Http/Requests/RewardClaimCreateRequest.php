<?php

namespace App\Http\Requests;

use App\Models\RewardClaim;
use Illuminate\Foundation\Http\FormRequest;

class RewardClaimCreateRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('create', RewardClaim::class);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		return [
			'reward_id' => 'required|uuid|exists:App\Models\Reward,id',
		];
	}
}
