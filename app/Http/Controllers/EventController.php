<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Department;
use App\Models\Event;
use App\Models\Reward;
use App\Models\TimeBonus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class EventController extends Controller {
	/**
	 * Display a listing of the resource.
	 */
	public function index(): JsonResponse {
		$this->authorize('viewAny', Event::class);
		return response()->json(['events' => Event::all()]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(EventStoreRequest $request): JsonResponse {
		$event = new Event($request->validated());
		$event->save();
		session()->flash('success', 'Event created.');
		return response()->json(['event' => $event]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Request $request, Event $event): JsonResponse|InertiaResponse {
		$this->authorize('view', $event);
		$user = $request->user();

		return $request->expectsJson()
			? response()->json(['event' => $event])
			: Inertia::render('EventCrud', [
				'event' => $event,
				'events' => fn () => $user->can('viewAny', Event::class) ? Event::orderBy('name')->get() : null,
				'departments' => fn () => Department::all(),
				'rewards' => fn () => $user->can('viewForEvent', [Reward::class, $event]) ? $event->rewards : null,
				'bonuses' => fn () => $user->can('viewForEvent', [TimeBonus::class, $event])
					? $event->timeBonuses()
						->with('departments', fn ($query) => $query->select('id'))
						->get()
						->map(fn ($bonus) => $bonus->toArrayWithDepartmentIds())
					: null,
			]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(EventUpdateRequest $request, Event $event): JsonResponse {
		$event->update($request->validated());
		return response()->json(['event' => $event]);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Event $event): JsonResponse {
		$this->authorize('delete', $event);

		// TODO: Once determination is made on what to do with soft-deletables, we may want to ensure relations get
		// deleted or soft-deleted along with the parent. At the moment, we just try to gracefully handle the parent,
		// Event in this case, being soft-deleted.
		$event->delete();

		return response()->json(null, 205);
	}
}
