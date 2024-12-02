import { computed, inject, onMounted, onUnmounted, ref, toRef, type InjectionKey } from 'vue';
import { usePage } from '@inertiajs/vue3';
import mitt from 'mitt';
import type SharedProps from '../data/SharedProps';

/**
 * Provides information about the application's settings/state from the Inertia page properties
 */
export function useAppSettings() {
	const page = usePage<SharedProps>();

	return {
		appName: inject(appNameInjectKey),
		activeEvent: toRef(() => page.props.activeEvent),
		timezone: toRef(() => page.props.timezone),
		kioskLifetime: toRef(() => page.props.kioskLifetime),
		isKiosk: toRef(() => page.props.isKiosk),
		isDevMode: toRef(() => page.props.isDevMode),
		isDebug: toRef(() => page.props.isDebug),
	};
}

/**
 * Key to use for injecting the Ziggy route helper
 */
export const appNameInjectKey = Symbol() as InjectionKey<string>;

/**
 * Provides convenient access to settings stored in localStorage
 */
export function useLocalSettings() {
	const timezone = localStorageValueToRef<Timezone>('timezone', 'app');
	const theme = localStorageValueToRef<Theme>('theme', 'system');

	return { timezone, theme };
}

export type Timezone = 'app' | 'local';
export type Theme = 'system' | 'light' | 'dark';

const localStorageBus = mitt<{ change: LocalStorageChangeEvent }>();
interface LocalStorageChangeEvent {
	key: string;
	val: string | number | boolean;
}

/**
 * Creates a ref for a localStorage value
 * @param key Key of the localStorage value
 * @param defaultVal Default value when the key doesn't exist
 */
function localStorageValueToRef<T extends LocalStorageChangeEvent['val']>(key: string, defaultVal: T) {
	const currentVal = localStorage.getItem(key);
	const setting = ref<T>(currentVal ? JSON.parse(currentVal) : defaultVal);

	// Listen to changes to the setting from elsewhere
	onMounted(() => {
		localStorageBus.on('change', onChange);
	});
	onUnmounted(() => {
		localStorageBus.off('change', onChange);
	});

	/**
	 * Listener for a setting being changed
	 */
	function onChange({ key: changedKey, val }: { key: string; val: LocalStorageChangeEvent['val'] }) {
		if (changedKey !== key) return;
		setting.value = val;
	}

	// Build a writable computed property to get/set the value of the setting
	return computed<T, T>({
		get() {
			return setting.value ?? defaultVal;
		},

		set(val) {
			if (!val) localStorage.removeItem(key);
			else localStorage.setItem(key, JSON.stringify(val));

			setting.value = val;
			localStorageBus.emit('change', { key, val });
		},
	});
}
