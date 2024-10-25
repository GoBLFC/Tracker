import type { RoleId } from './Role';
import type TimeEntry from './TimeEntry';

export default interface User {
	id: UserId;
	username: string;
	badge_id: number;
	badge_name: string | null;
	first_name: string | null;
	last_name: string | null;
	full_name: string | null;
	role: RoleId;
	time_entries?: TimeEntry[];
}

export type UserId = string & { __userIdType: never };
