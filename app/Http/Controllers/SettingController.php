<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingUpdateRequest;
use App\Models\Event;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SettingController extends Controller {
	public function index(Request $request): JsonResponse|InertiaResponse {
		$this->authorize('viewAny', Setting::class);

		$data = [
			'settings' => Setting::all(),
			'events' => Event::all(),
		];

		return $request->expectsJson()
			? response()->json($data)
			: Inertia::render('Config', $data);
	}

	/**
	 * Update the value of a setting
	 */
	public function update(SettingUpdateRequest $request, Setting $setting): JsonResponse|RedirectResponse {
		$setting->setValue($request->validated('value'));

		$success = '';
		switch ($setting->name) {
			case 'active-event':
				$success = ($request->validated('value') !== null ? 'Updated' : 'Cleared') . ' the active event.';
				break;
			case 'dev-mode':
				$success = ($request->boolean('value') ? 'Enabled' : 'Disabled') . ' development mode.';
				break;
			case 'lockdown':
				$success = $request->boolean('value') ? 'Locked the site down.' : 'Lifted the lockdown.';
				break;
			default:
				$success = "Updated the value of the {$setting->name} setting.";
		}

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess($success);
	}

	/**
	 * Clear the value of a setting
	 */
	public function destroy(Request $request, Setting $setting): JsonResponse|RedirectResponse {
		$this->authorize('update', $setting);

		$setting->setValue(null);

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess("Cleared the value of the {$setting->name} setting.");
	}
}
