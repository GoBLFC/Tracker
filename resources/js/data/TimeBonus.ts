import type { DepartmentId } from './Department';
import type { EventId } from './Event';

export default interface TimeBonus {
	id: TimeBonusId;
	start: string;
	stop: string;
	modifier: number;
	departments?: DepartmentId[] | null;
	event_id: EventId;
}

export type TimeBonusId = string & { __timeBonusIdType: never };
