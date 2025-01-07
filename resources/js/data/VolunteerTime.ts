import type TimeEntry from './TimeEntry';

export default interface VolunteerTime {
	total: number;
	bonus: number;
	day: number;
	entries: TimeEntry[];
}
