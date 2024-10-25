import type { RewardId } from './Reward';
import type { UserId } from './User';

export default interface RewardClaim {
	id: RewardClaimId;
	reward_id: RewardId;
	user_id: UserId;
}

export type RewardClaimId = string & { __rewardClaimIdType: never };
