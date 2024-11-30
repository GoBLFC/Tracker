import { toRef } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type SharedProps from './SharedProps';

/**
 * Provides information about the application's settings/state from the Inertia page properties
 */
export function useSettings() {
	const page = usePage<SharedProps>();

	return {
		activeEvent: toRef(() => page.props.activeEvent),
		timezone: toRef(() => page.props.timezone),
		kioskLifetime: toRef(() => page.props.kioskLifetime),
		isKiosk: toRef(() => page.props.isKiosk),
		isDevMode: toRef(() => page.props.isDevMode),
		isDebug: toRef(() => page.props.isDebug),
	};
}
