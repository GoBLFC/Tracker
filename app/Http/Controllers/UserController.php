<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserSearchRequest;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

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

		return response()->json([
			'user' => $user,
		]);
	}

	/**
	 * Search all users by their badge ID, username, badge name, or real name
	 */
	public function getSearch(UserSearchRequest $request): JsonResponse {
		$query = $request->input('q');
		$wildQuery = "%{$query}%";
		return response()->json([
			'users' => User::whereBadgeId($query)
				->orWhere('username', 'like', $wildQuery)
				->orWhere('badge_name', 'like', $wildQuery)
				->orWhere('first_name', 'like', $wildQuery)
				->orWhere('last_name', 'like', $wildQuery)
				->with([
					'timeEntries' => function (Builder $query) {
						$query->ongoing()->forEvent();
					},
					'timeEntries.department',
				])
				->limit(20)
				->get(),
		]);
	}
}
