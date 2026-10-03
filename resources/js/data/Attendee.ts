import type { UserId } from './User';

export default interface Attendee {
	id: UserId;
	badge_id: number;
	badge_name: string | null;
	pivot: {
		type: 'attendee' | 'gatekeeper';
		created_at: string;
		/** User that let the attendee in despite the log's entry requirements */
		overridden_by_id?: UserId | null;
		/** Optional explanation given for the override */
		override_reason?: string | null;
	};
}

/** Basic details of users that overrode entry requirements, keyed by user ID */
export type Overriders = Record<UserId, Pick<Attendee, 'id' | 'badge_id' | 'badge_name'>>;
