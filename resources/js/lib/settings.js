import { toRef } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Provides information about the application's settings/state from the Inertia page properties
 */
export function useSettings() {
	const page = usePage();

	return {
		activeEvent: toRef(() => page.props.activeEvent),
		timezone: toRef(() => page.props.timezone),
		isKiosk: toRef(() => page.props.isKiosk),
		isDevMode: toRef(() => page.props.isDevMode),
		isDebug: toRef(() => page.props.isDebug),
		hasDebugbar: toRef(() => page.props.hasDebugbar),
	};
}
