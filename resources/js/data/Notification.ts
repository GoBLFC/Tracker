export default interface Notification {
	id: NotificationId;
	title: string;
	description: string;
	read_at: string;
	created_at: string;
}

export type NotificationId = string & { __notificationIdType: never };
