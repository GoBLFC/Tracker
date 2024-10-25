import type TimeEntry from './TimeEntry';

export default interface TimeStats {
	day: number;
	total: number;
	entries: TimeEntry[];
}
