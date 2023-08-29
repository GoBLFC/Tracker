<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingSetRequest extends FormRequest {
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
		switch ($this->setting->id) {
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
		switch ($this->setting->id) {
			case 'dev-mode':
			case 'lockdown':
				$this->replace(['value' => filter_var($this->input('value'), FILTER_VALIDATE_BOOL)]);
				break;
		}
	}
}
