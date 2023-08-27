<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\SettingSetRequest;

class SettingsController extends Controller {
	/**
	 * Update the value of a setting
	 */
	public function putSetting(SettingSetRequest $request, Setting $setting): JsonResponse|RedirectResponse {
		$setting->setValue($request->input('value'));
		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess("Updated value of {$setting->id} setting.");
	}

	/**
	 * Clear the value of a setting
	 */
	public function deleteSetting(Request $request, Setting $setting): JsonResponse|RedirectResponse {
		$this->authorize('update', $setting);
		$setting->setValue(null);
		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess("Cleared value of {$setting->id} setting.");
	}
}
