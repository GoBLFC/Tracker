<?php

namespace App\Http\Controllers;

use App\Http\Requests\RewardStoreRequest;
use App\Http\Requests\RewardUpdateRequest;
use App\Models\Event;
use App\Models\Reward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class RewardController extends Controller {
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request, Event $event): JsonResponse|InertiaResponse {
		$this->authorize('viewForEvent', [Reward::class, $event]);

		if ($request->expectsJson()) return response()->json(['rewards' => $event->rewards]);

		/** @var User */
		$user = $request->user();
		return Inertia::render('EventCrud', [
			'event' => fn () => $event->toResource(),
			'events' => fn () => $user->can('viewAny', Event::class)
				? Event::orderBy('name')->get(['id', 'name'])->toResourceCollection()
				: null,
			'rewards' => $event->rewards()->get(['id', 'name', 'description', 'hours'])->toResourceCollection(),
		]);
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
