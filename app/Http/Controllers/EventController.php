<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;

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
	public function show(Event $event): JsonResponse {
		$this->authorize('view', $event);
		return response()->json(['event' => $event]);
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
