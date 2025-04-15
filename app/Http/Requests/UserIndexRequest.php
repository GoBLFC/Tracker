<?php

namespace App\Http\Requests;

use App\Models\User;

class UserIndexRequest extends PaginateRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('viewAny', User::class);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		return array_merge(parent::rules(), [
			'sortBy' => 'sometimes|nullable|string|in:display_name,badge_id,role,created_at',
			'sortDir' => 'sometimes|nullable|string|in:asc,desc',
			'name' => 'sometimes|nullable|string|max:64',
			'badge_id' => 'sometimes|nullable|integer',
			'role' => 'sometimes|nullable|integer|between:-2,4',
		]);
	}

	/**
	 * Handle a passed validation attempt.
	 */
	protected function passedValidation(): void {
		parent::passedValidation();
		$this->mergeIfMissing([
			'sortBy' => 'display_name',
			'sortDir' => 'asc',
		]);
	}
}
