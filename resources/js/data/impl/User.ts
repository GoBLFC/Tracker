import TimeEntry from './TimeEntry';
import type { RoleId } from '../Role';
import type { UserId } from '../User';
import type RawUser from '../User';

export default class User {
	id: UserId;
	username: string;
	badge_id: number;
	badge_name: string | null;
	first_name: string | null;
	last_name: string | null;
	role: RoleId;
	time_entries?: TimeEntry[] | undefined;

	constructor(raw: RawUser) {
		this.id = raw.id;
		this.username = raw.username;
		this.badge_id = raw.badge_id;
		this.badge_name = raw.badge_name;
		this.first_name = raw.first_name;
		this.last_name = raw.last_name;
		this.role = raw.role;
		this.time_entries = raw.time_entries ? TimeEntry.load(raw.time_entries) : undefined;
	}

	get display_name() {
		return this.badge_name ?? this.username;
	}

	get full_name() {
		return `${this.first_name} ${this.last_name}`;
	}

	static load(raw: RawUser[]) {
		return raw.map((raw) => new User(raw));
	}
}
