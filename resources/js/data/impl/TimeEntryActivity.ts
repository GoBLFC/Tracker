import TimeEntry from './TimeEntry';
import type { TimeEntryActivityId } from '../TimeEntryActivity';
import type RawTimeEntryActivity from '../TimeEntryActivity';

export default class TimeEntryActivity {
	id: TimeEntryActivityId;
	subject: TimeEntry;
	properties: { attributes: { stop?: number | null } };

	constructor(raw: RawTimeEntryActivity) {
		this.id = raw.id;
		this.subject = new TimeEntry(raw.subject);
		this.properties = raw.properties;
	}

	get checked_in() {
		return !this.properties.attributes.stop;
	}

	static load(raw: RawTimeEntryActivity[]) {
		return raw.map((raw) => new TimeEntryActivity(raw));
	}
}
