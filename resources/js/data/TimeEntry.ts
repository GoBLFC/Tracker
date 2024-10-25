import type Department from './Department';
import type User from './User';

export default interface TimeEntry {
	id: TimeEntryId;
	start: string;
	stop: string | null;
	notes: string | null;
	auto: boolean;
	user: User;
	department: Department;
}

export type TimeEntryId = string & { __timeEntryIdType: never };
