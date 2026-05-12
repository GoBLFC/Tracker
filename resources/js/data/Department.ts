import type { EventId } from './Event';

export default interface Department {
	id: DepartmentId;
	name: string;
	hidden: boolean;
	event_id: EventId;
}

export type DepartmentId = string & { __departmentIdType: never };
