<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeBonusStoreRequest;
use App\Http\Requests\TimeBonusUpdateRequest;
use App\Models\Event;
use App\Models\TimeBonus;
use Illuminate\Http\JsonResponse;

class TimeBonusController extends Controller {
	public function __construct() {
		$this->authorizeResource(TimeBonus::class, 'bonus');
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(Event $event): JsonResponse {
		return response()->json(['bonuses' => $event->timeBonuses]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(TimeBonusStoreRequest $request, Event $event): JsonResponse {
		$bonus = new TimeBonus($request->validated());
		$bonus->event_id = $event->id;
		$bonus->save();
		return response()->json(['bonus' => $bonus]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(TimeBonus $bonus): JsonResponse {
		return response()->json(['bonus' => $bonus]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(TimeBonusUpdateRequest $request, TimeBonus $bonus): JsonResponse {
		$bonus->update($request->validated());
		return response()->json(['bonus' => $bonus]);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(TimeBonus $bonus): JsonResponse {
		$bonus->delete();
		return response()->json(null, 205);
	}
}
