<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeBonusStoreRequest;
use App\Http\Requests\TimeBonusUpdateRequest;
use App\Models\Event;
use App\Models\TimeBonus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TimeBonusController extends Controller {
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request, Event $event): JsonResponse|RedirectResponse {
		$this->authorize('viewForEvent', [TimeBonus::class, $event]);
		return $request->expectsJson()
			? response()->json(['bonuses' => $event->timeBonuses()->with('departments')->get()])
			: redirect()->route('events.show', $event);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(TimeBonusStoreRequest $request, Event $event): JsonResponse|RedirectResponse {
		$bonus = new TimeBonus;
		$bonus->start = Carbon::parse($request->input('start'))->timezone(config('app.timezone'));
		$bonus->stop = Carbon::parse($this->input('stop'))->timezone(config('app.timezone'));
		$bonus->modifier = $request->float('modifier');
		$bonus->event_id = $event->id;
		$bonus->save();

		$bonus->departments()->sync($request->validated('departments'));

		return $request->expectsJson()
			? response()->json(['bonus' => $bonus])
			: redirect()->back()->withSuccess('Created time bonus.');
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
	public function update(TimeBonusUpdateRequest $request, TimeBonus $bonus): JsonResponse|RedirectResponse {
		$changes = $request->validated();
		$departments = isset($changes['departments']) ? $changes['departments'] : null;
		unset($changes['departments']);

		if (isset($changes['start'])) $changes['start'] = Carbon::parse($changes['start'])->timezone(config('app.timezone'));
		if (isset($changes['stop'])) $changes['stop'] = Carbon::parse($changes['stop'])->timezone(config('app.timezone'));
		$bonus->update($changes);

		if ($departments !== null) $bonus->departments()->sync($request->validated('departments'));

		return $request->expectsJson()
			? response()->json(['bonus' => $bonus])
			: redirect()->back()->withSuccess('Updated time bonus.');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, TimeBonus $bonus): JsonResponse|RedirectResponse {
		$this->authorize('delete', $bonus);

		$bonus->delete();

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess('Deleted time bonus.');
	}
}
