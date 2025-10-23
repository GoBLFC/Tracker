<?php

namespace App\Http\Controllers;

use App\Http\Requests\RewardStoreRequest;
use App\Http\Requests\RewardUpdateRequest;
use App\Models\Event;
use App\Models\Reward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RewardController extends Controller {
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request, Event $event): JsonResponse|RedirectResponse {
		$this->authorize('viewForEvent', [Reward::class, $event]);
		return $request->expectsJson()
			? response()->json(['rewards' => $event->rewards])
			: redirect()->route('events.show', $event);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(RewardStoreRequest $request, Event $event): JsonResponse|RedirectResponse {
		$reward = new Reward($request->validated());
		$reward->event_id = $event->id;
		$reward->save();

		return $request->expectsJson()
			? response()->json(['reward' => $reward])
			: redirect()->back()->withSuccess("Created reward {$reward->name}.");
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Reward $reward): JsonResponse {
		$this->authorize('view', $reward);
		return response()->json(['reward' => $reward]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(RewardUpdateRequest $request, Reward $reward): JsonResponse|RedirectResponse {
		$reward->update($request->validated());

		return $request->expectsJson()
			? response()->json(['reward' => $reward])
			: redirect()->back()->withSuccess("Updated reward {$reward->name}.");
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, Reward $reward): JsonResponse|RedirectResponse {
		$this->authorize('delete', $reward);

		$reward->delete();

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess("Deleted reward {$reward->name}.");
	}
}
