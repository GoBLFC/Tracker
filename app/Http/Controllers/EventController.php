<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;

class EventController extends Controller {
	public function __construct() {
		$this->authorizeResource(Event::class, 'event');
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): JsonResponse {
		return response()->json(['events' => Event::all()]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(EventStoreRequest $request): JsonResponse {
		$event = new Event($request->validated());
		$event->save();
		return response()->json(['event' => $event]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Event $event): JsonResponse {
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
		$event->delete();
		return response()->json(null, 205);
	}
}
