<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeBonusStoreRequest;
use App\Http\Requests\TimeBonusUpdateRequest;
use App\Models\Event;
use App\Models\TimeBonus;
use Illuminate\Http\JsonResponse;

class TimeBonusController extends Controller {
	/**
	 * Display a listing of the resource.
	 */
	public function index(Event $event): JsonResponse {
		$this->authorize('viewAny', TimeBonus::class);
		return response()->json(['bonuses' => $event->timeBonuses()->with('departments')->get()]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(TimeBonusStoreRequest $request, Event $event): JsonResponse {
		$bonus = new TimeBonus($request->safe()->except('departments'));
		$bonus->event_id = $event->id;
		$bonus->save();
		$bonus->departments()->sync($request->validated('departments'));
		session()->flash('success', 'Time bonus created.');
		return response()->json(['bonus' => $bonus]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(TimeBonus $bonus): JsonResponse {
		$this->authorize('view', $bonus);
		return response()->json(['bonus' => $bonus]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(TimeBonusUpdateRequest $request, TimeBonus $bonus): JsonResponse {
		$bonus->update($request->safe()->except('departments'));
		$bonus->departments()->sync($request->validated('departments'));
		return response()->json(['bonus' => $bonus]);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(TimeBonus $bonus): JsonResponse {
		$this->authorize('delete', $bonus);
		$bonus->delete();
		return response()->json(null, 205);
	}
}
