<?php

namespace App\Http\Controllers;

use App\Http\Requests\RewardClaimStoreRequest;
use App\Models\Event;
use App\Models\Reward;
use App\Models\RewardClaim;
use App\Models\User;
use Illuminate\Http\JsonResponse;

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
		// Authorize the creation based on the target reward
		$rewardId = $request->validated('reward_id');
		$reward = Reward::whereId($rewardId)->with('event')->firstOrFail();
		$this->authorize('create', [RewardClaim::class, $reward]);

		// Make sure there isn't an existing claim
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
