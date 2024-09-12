<?php

namespace App\Http\Controllers;

use App\Http\Requests\RewardStoreRequest;
use App\Http\Requests\RewardUpdateRequest;
use App\Models\Event;
use App\Models\Reward;
use Illuminate\Http\JsonResponse;

class RewardController extends Controller {
	public function __construct() {
		$this->authorizeResource(Reward::class, 'reward');
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(Event $event): JsonResponse {
		return response()->json(['rewards' => $event->rewards]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(RewardStoreRequest $request, Event $event): JsonResponse {
		$reward = new Reward($request->validated());
		$reward->event_id = $event->id;
		$reward->save();
		session()->flash('success', 'Reward created.');
		return response()->json(['reward' => $reward]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Reward $reward): JsonResponse {
		return response()->json(['reward' => $reward]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(RewardUpdateRequest $request, Reward $reward): JsonResponse {
		$reward->update($request->validated());
		return response()->json(['reward' => $reward]);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Reward $reward): JsonResponse {
		$reward->delete();
		return response()->json(null, 205);
	}
}
