import User from './User';
import type { UserId } from '../User';
import type Department from '../Department';
import type { TimeEntryId } from '../TimeEntry';
import type RawTimeEntry from '../TimeEntry';
import type { EventId } from '../Event';

export default class TimeEntry {
	id: TimeEntryId;
	start: Date;
	stop: Date | null;
	notes: string | null;
	auto: boolean;
	event_id: EventId;
	user_id: UserId;
	user?: User;
	department: Department;
	bonus_time?: number;

	constructor(raw: RawTimeEntry) {
		this.id = raw.id;
		this.start = new Date(raw.start);
		this.stop = raw.stop ? new Date(raw.stop) : null;
		this.notes = raw.notes;
		this.auto = raw.auto;
		this.event_id = raw.event_id;
		this.user_id = raw.user_id;
		this.user = raw.user ? new User(raw.user) : undefined;
		this.department = raw.department;
		this.bonus_time = raw.bonus_time;
	}

	get duration() {
		return ((this.stop ?? new Date()).getTime() - this.start.getTime()) / 1000;
	}

	get earned() {
		return this.bonus_time ? this.duration + this.bonus_time : this.duration;
	}

	static load(raw: RawTimeEntry[]) {
		return raw.map((raw) => new TimeEntry(raw));
	}
}
