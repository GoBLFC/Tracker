export default interface Reward {
	id: RewardId;
	name: string;
	description: string;
	hours: number;
}

export type RewardId = string & { __rewardIdType: never };
