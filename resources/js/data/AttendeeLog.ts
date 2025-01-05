import type Attendee from './Attendee';
import type { EventId } from './Event';

export default interface AttendeeLog {
	id: AttendeeLogId;
	name: string;
	event_id: EventId;
	users?: Attendee[];
	users_count?: number;
	attendees_count?: number;
	gatekeepers_count?: number;
}

export type AttendeeLogId = string & { __attendeeLogIdType: never };
