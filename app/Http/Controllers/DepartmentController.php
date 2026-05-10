<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentStoreRequest;
use App\Http\Requests\DepartmentUpdateRequest;
use App\Models\Department;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller {
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request, Event $event): JsonResponse|RedirectResponse {
		$this->authorize('viewForEvent', [Department::class, $event]);
		return $request->expectsJson()
			? response()->json(['departments' => $event->departments])
			: redirect()->route('events.show', $event);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(DepartmentStoreRequest $request, Event $event): JsonResponse|RedirectResponse {
		$department = new Department($request->validated());
		$department->event_id = $event->id;
		$department->save();

		return $request->expectsJson()
			? response()->json(['department' => $department])
			: redirect()->back()->withSuccess("Created department {$department->name}.");
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Department $department): JsonResponse {
		$this->authorize('view', $department);
		return response()->json(['department' => $department]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(DepartmentUpdateRequest $request, Department $department): JsonResponse|RedirectResponse {
		$department->update($request->validated());

		return $request->expectsJson()
			? response()->json(['department' => $department])
			: redirect()->back()->withSuccess("Updated department {$department->name}.");
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, Department $department): JsonResponse|RedirectResponse {
		$this->authorize('delete', $department);

		// TODO: Once determination is made on what to do with soft-deletables, we may want to ensure relations get
		// deleted or soft-deleted along with the parent. At the moment, we just try to gracefully handle the parent,
		// Department in this case, being soft-deleted.
		$department->delete();

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess("Deleted department {$department->name}.");
	}
}
