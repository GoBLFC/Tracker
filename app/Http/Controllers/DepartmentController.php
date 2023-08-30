<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentStoreRequest;
use App\Http\Requests\DepartmentUpdateRequest;
use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller {
	public function __construct() {
		$this->authorizeResource(Department::class, 'department');
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): JsonResponse {
		return response()->json(['departments' => Department::all()]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(DepartmentStoreRequest $request): JsonResponse {
		$department = new Department($request->validated());
		$department->save();
		return response()->json(['department' => $department]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Department $department): JsonResponse {
		return response()->json(['department' => $department]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(DepartmentUpdateRequest $request, Department $department): JsonResponse {
		$department->update($request->validated());
		return response()->json(['department' => $department]);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Department $department): JsonResponse {
		$department->delete();
		return response()->json(null, 205);
	}
}
