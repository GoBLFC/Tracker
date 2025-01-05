import type { UserId } from './User';

export default interface Attendee {
	id: UserId;
	badge_id: number;
	badge_name: string | null;
	pivot: {
		type: 'attendee' | 'gatekeeper';
		created_at: string;
	};
}
