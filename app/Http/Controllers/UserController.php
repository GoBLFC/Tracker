<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserIndexRequest;
use App\Http\Requests\UserSearchRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class UserController extends Controller {
	public function index(UserIndexRequest $request): JsonResponse|InertiaResponse {
		$sortBy = $request->string('sortBy');
		$sortDir = $request->string('sortDir');
		$sortByName = $sortBy->exactly('display_name');

		$query = User::query();

		// Sort by the requested field (if any), followed by the display name
		if (!$sortByName) $query->orderBy($sortBy, $sortDir);
		$query->orderBy(
			DB::raw('case when "badge_name" is not null then "badge_name" else "username" end'),
			$sortByName ? $sortDir : 'asc',
		);

		// Filter by the requested field(s)
		if ($request->has('badge_id')) $query->whereBadgeId($request->integer('badge_id'));
		if ($request->has('role')) $query->whereRole($request->integer('role'));
		if ($request->has('name')) {
			$wildSearch = Str::lower("%{$request->name}%");
			$query->where(function (Builder $query) use ($wildSearch) {
				$query->whereRaw('lower(username) like ?', $wildSearch)
					->orWhereRaw('lower(badge_name) like ?', $wildSearch)
					->orWhereRaw('lower(first_name) like ?', $wildSearch)
					->orWhereRaw('lower(last_name) like ?', $wildSearch);
			});
		}

		// Run the query with paginated results
		$users = $query->paginate($request->integer('count'));
		$data = [
			'users' => $users->items(),
			'total' => $users->total(),
			'perPage' => $users->perPage(),
			'first' => $users->firstItem(),
			'sortBy' => $sortBy,
			'sortDir' => $sortDir,
			'filters' => [
				'name' => $request->string('name'),
				'badge_id' => $request->has('badge_id') ? $request->integer('badge_id') : null,
				'role' => $request->has('role') ? $request->integer('role') : null,
			],
		];

		return $request->expectsJson()
			? response()->json($data)
			: Inertia::render('Users', $data);
	}

	/**
	 * Create a user with just a badge ID
	 */
	public function store(UserStoreRequest $request): JsonResponse|RedirectResponse {
		$user = User::createWithAvailableDetails($request->integer('badge_id'), 'Volunteer');

		return $request->expectsJson()
			? response()->json(['user' => $user])
			: redirect()->back()->withSuccess("User {$user->audit_name} created.");
	}

	/**
	 * Update a user's details
	 */
	public function update(UserUpdateRequest $request, User $user): JsonResponse|RedirectResponse {
		$authUser = $request->user();

		// Prevent modifying a user of a higher role
		if ($authUser->role->value < $user->role->value) {
			return
				$request->expectsJson()
					? response()->json(['error' => 'Cannot modify a user of a higher role than you.'])
					: redirect()->back()->withError('Cannot modify a user of a higher role than you.');
		}

		// Protect against undesirable role changes
		if ($request->has('role')) {
			if ($user->id === $authUser->id) {
				return $request->expectsJson()
					? response()->json(['error' => 'Cannot change your own role.'], 403)
					: redirect()->back()->withError('Cannot change your own role.');
			}

			if ($authUser->role->value < $request->enum('role', Role::class)->value) {
				return $request->expectsJson()
					? response()->json(['error' => 'Cannot change a user to a higher role than your own.'], 403)
					: redirect()->back()->withError('Cannot change a user to a higher role than your own.');
			}
		}

		$user->update($request->validated());

		return $request->expectsJson()
			? response()->json(['user' => $user])
			: redirect()->back()->withSuccess("User {$user->audit_name} updated.");
	}

	/**
	 * Delete a user
	 */
	public function destroy(Request $request, User $user): JsonResponse|RedirectResponse {
		$this->authorize('delete', $user);

		// Don't allow a user to delete themself
		if ($user->id === $request->user()->id) {
			return $request->expectsJson()
				? response()->json(['error' => 'Cannot delete yourself.'], 403)
				: redirect()->back()->withError('Cannot delete yourself.');
		}

		$user->delete();

		return $request->expectsJson()
			? response()->json(null, 205)
			: redirect()->back()->withSuccess("User {$user->audit_name} deleted.");
	}

	/**
	 * Search all users by their badge ID, username, badge name, or real name
	 */
	public function getSearch(UserSearchRequest $request): JsonResponse {
		$this->authorize('viewAny', User::class);

		// Get the search string and the search string prepped for an SQL LIKE comparison
		$search = $request->validated('q');
		$wildSearch = Str::lower("%{$search}%");

		// Build the search query
		$query = User::whereRaw('lower(username) like ?', $wildSearch)
			->orWhereRaw('lower(badge_name) like ?', $wildSearch)
			->orWhereRaw('lower(first_name) like ?', $wildSearch)
			->orWhereRaw('lower(last_name) like ?', $wildSearch)
			->with([
				'timeEntries' => function (Builder $query) {
					$query->ongoing()->forEvent();
				},
				'timeEntries.department',
			])
			->limit(20);

		// Only add the where clause to match the exact badge ID if the search string is an integer
		if (filter_var($search, FILTER_VALIDATE_INT)) $query->orWhere('badge_id', $search);

		return response()->json(['users' => $query->get()]);
	}
}
