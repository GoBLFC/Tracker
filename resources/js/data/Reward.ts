export default interface Reward {
	id: RewardId;
	name: string;
	hours: number;
}

export type RewardId = string & { __rewardIdType: never };
