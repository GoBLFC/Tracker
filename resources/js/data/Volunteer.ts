import type RewardClaim from './RewardClaim';
import type TimeStats from './TimeStats';
import type User from './User';

export default interface Volunteer {
	user: User;
	stats: TimeStats;
	claims: RewardClaim[];
}
