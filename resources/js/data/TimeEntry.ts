import type Department from './Department';
import type User from './User';
import type { UserId } from './User';
import type { EventId } from './Event';

export default interface TimeEntry {
	id: TimeEntryId;
	start: string;
	stop: string | null;
	notes: string | null;
	auto: boolean;
	event_id: EventId;
	user_id: UserId;
	user?: User;
	department: Department;
	bonus_time?: number;
}

export type TimeEntryId = string & { __timeEntryIdType: never };
