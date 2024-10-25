export default interface Event {
	id: EventId;
	name: string;
}

export type EventId = string & { __eventIdType: never };
