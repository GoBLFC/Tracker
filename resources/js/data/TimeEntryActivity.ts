import type TimeEntry from './TimeEntry';

export default interface TimeEntryActivity {
	id: TimeEntryActivityId;
	subject: TimeEntry;
	properties: {
		attributes: {
			stop?: number | null;
		};
	};
}

export type TimeEntryActivityId = string & { __timeEntryActivityIdType: never };
