import { useAppSettings } from './settings';
import { useUser } from './user';
import type Event from '@/data/Event';

/**
 * Helper to check for read-only status of events
 */
export function useReadOnly() {
	const { activeEvent } = useAppSettings();
	const { isAdmin } = useUser();

	/**
	 * Checks whether an event is read-only to the current user
	 */
	function isReadOnly(event: Event) {
		return !isAdmin.value && event.id !== activeEvent.value?.id;
	}

	return isReadOnly;
}
