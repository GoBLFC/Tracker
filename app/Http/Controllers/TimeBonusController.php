<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeBonusStoreRequest;
use App\Http\Requests\TimeBonusUpdateRequest;
use App\Models\Event;
use App\Models\TimeBonus;

class TimeBonusController extends Controller {
	public function __construct() {
		$this->authorizeResource(TimeBonus::class, 'bonus');
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(Event $event) {
		return response()->json(['bonuses' => $event->timeBonuses]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(TimeBonusStoreRequest $request, Event $event) {
		$bonus = new TimeBonus($request->validated());
		$bonus->event_id = $event->id;
		$bonus->save();
		return response()->json(['bonus' => $bonus]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(TimeBonus $bonus) {
		return response()->json(['bonus' => $bonus]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(TimeBonusUpdateRequest $request, TimeBonus $bonus) {
		$bonus->update($request->validated());
		return response()->json(['bonus' => $bonus]);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(TimeBonus $bonus) {
		$bonus->delete();
		return response()->json(null, 205);
	}
}
