import type RewardClaim from './RewardClaim';
import type VolunteerTime from './VolunteerTime';
import type User from './User';

export default interface Volunteer {
	user: User;
	time: VolunteerTime;
	reward_claims: RewardClaim[];
}
