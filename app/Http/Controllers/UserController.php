<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserSearchRequest;
use Illuminate\Contracts\Database\Eloquent\Builder;

class UserController extends Controller {
	/**
	 * Create a user with just a badge ID
	 */
	public function postCreate(UserCreateRequest $request): JsonResponse {
		$user = new User;
		$user->badge_id = $request->input('badge_id');
		$user->username = 'Unknown';
		$user->first_name = 'Unknown';
		$user->last_name = 'Unknown';
		$user->save();

		return response()->json(['user' => $user]);
	}

	/**
	 * Search all users by their badge ID, username, badge name, or real name
	 */
	public function getSearch(UserSearchRequest $request): JsonResponse {
		// Get the search string and the search string prepped for an SQL LIKE comparison
		$search = $request->input('q');
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
