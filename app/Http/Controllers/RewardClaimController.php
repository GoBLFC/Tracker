<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\RewardClaim;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\RewardClaimStoreRequest;

class RewardClaimController extends Controller {
	/**
	 * Get reward claims for a user
	 */
	public function getClaims(User $user, ?Event $event = null): JsonResponse {
		$this->authorize('viewAny', [RewardClaim::class, $user]);
		return response()->json([
			'reward_claims' => $user->rewardClaims()->forEvent($event)->get(),
		]);
	}

	/**
	 * Claim a reward for a user
	 */
	public function store(RewardClaimStoreRequest $request, User $user): JsonResponse {
		// Make sure there isn't an existing claim
		$rewardId = $request->input('reward_id');
		if ($user->rewardClaims()->whereRewardId($rewardId)->exists()) {
			return response()->json(['error' => 'Reward is already claimed by user.'], 422);
		}

		// Create a claim
		$claim = new RewardClaim;
		$claim->user_id = $user->id;
		$claim->reward_id = $rewardId;
		$claim->save();

		return response()->json(['reward_claim' => $claim]);
	}

	/**
	 * Unclaim a reward for a user
	 */
	public function destroy(RewardClaim $rewardClaim): JsonResponse {
		$this->authorize('delete', $rewardClaim);
		$rewardClaim->delete();
		return response()->json(null, 205);
	}
}
