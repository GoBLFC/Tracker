<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingUpdateRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return $this->user()->can('update', $this->route('setting'));
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array {
		switch ($this->setting->name) {
			case 'active-event':
				return ['value' => 'present|nullable|uuid|exists:App\Models\Event,id'];
			case 'dev-mode':
			case 'lockdown':
				return ['value' => 'present|nullable|boolean'];
			default:
				return [];
		}
	}

	/**
	 * Handle a passed validation attempt.
	 */
	protected function passedValidation(): void {
		switch ($this->setting->name) {
			case 'dev-mode':
			case 'lockdown':
				$this->replace(['value' => $this->boolean('value')]);
				break;
		}
	}
}
