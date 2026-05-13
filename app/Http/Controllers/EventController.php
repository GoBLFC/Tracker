<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Department;
use App\Models\Event;
use App\Models\Reward;
use App\Models\Setting;
use App\Models\TimeBonus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class EventController extends Controller {
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request): JsonResponse|InertiaResponse|RedirectResponse {
		$this->authorize('viewAny', Event::class);

		if ($request->expectsJson()) return response()->json(['events' => Event::all()]);

		$event = Setting::activeEvent();
		if ($event) {
			$request->session()->reflash();
			return redirect()->route('events.show', [$event->id]);
		}

		return Inertia::render('EventCrud', [
			'event' => null,
			'events' => Event::orderBy('name')->get(),
			'departments' => null,
			'rewards' => null,
			'bonuses' => null,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(EventStoreRequest $request): JsonResponse|RedirectResponse {
		$event = new Event($request->validated());
		$event->id = $event->newUniqueId();

		// Clone departments from another event and use a transaction to store them and the event itself together
		if ($request->has('cloneEvent')) {
			$newDepartments = [];
			$sourceDepartments = Department::forEvent($request->input('cloneEvent'))->get(['name', 'hidden']);
			foreach ($sourceDepartments as $source) {
				$newDepartments[] = ['name' => $source->name, 'hidden' => $source->hidden, 'event_id' => $event->id];
			}

			DB::transaction(function () use ($event, $newDepartments) {
				$event->save();
				Department::fillAndInsert($newDepartments);
			});
		} else {
			$event->save();
		}

		return $request->expectsJson()
			? response()->json(['event' => $event])
			: redirect()->route('events.show', [$event->id])->withSuccess("Created event {$event->name}.");
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
				'departments' => fn () => $user->can('viewForEvent', [Department::class, $event]) ? $event->departments : null,
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
	public function update(EventUpdateRequest $request, Event $event): JsonResponse|RedirectResponse {
		$event->update($request->validated());
		return $request->expectsJson()
			? response()->json(['event' => $event])
			: redirect()->back()->withSuccess('Renamed event.');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, Event $event): JsonResponse|RedirectResponse {
		$this->authorize('delete', $event);

		$isActive = $event->isActive();

		// TODO: Once determination is made on what to do with soft-deletables, we may want to ensure relations get
		// deleted or soft-deleted along with the parent. At the moment, we just try to gracefully handle the parent,
		// Event in this case, being soft-deleted.
		$event->delete();

		if ($isActive) Setting::set('active-event', null);

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->route('events.index')->withSuccess("Deleted event {$event->name}.");
	}
}
