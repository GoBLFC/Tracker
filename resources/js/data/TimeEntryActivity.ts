import type TimeEntry from './TimeEntry';

export default interface TimeEntryActivity {
	id: TimeEntryActivityId;
	subject: TimeEntry;
	properties: {
		attributes: {
			stop?: number | null;
		};
	};
	created_at: string;
}

export type TimeEntryActivityId = string & { __timeEntryActivityIdType: never };
