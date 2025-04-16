export default interface User {
	id: SettingId;
	name: string;
	value: string;
}

export type SettingId = string & { __userIdType: never };
