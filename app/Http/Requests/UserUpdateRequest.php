<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UserUpdateRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('update', $this->route('user'));
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		return [
			'badge_id' => 'sometimes|integer|digits_between:1,8|unique:App\Models\User,badge_id',
			'badge_name' => 'sometimes|string|max:64',
			'first_name' => 'sometimes|string|max:64',
			'last_name' => 'sometimes|string|max:64',
			'role' => ['sometimes', new Enum(Role::class)],
		];
	}
}
