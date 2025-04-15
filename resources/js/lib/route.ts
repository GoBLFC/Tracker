import { inject } from 'vue';
import type { InjectionKey } from 'vue';
import type { route as routeFn } from 'vendor/tightenco/ziggy';

/**
 * Parameters for a Ziggy route call
 */
export type ZiggyParams = Parameters<typeof routeFn>;

/**
 * Type for a Ziggy route
 */
export type RouteName = Parameters<typeof routeFn>[0];

/**
 * Key to use for injecting the Ziggy route helper
 */
export const injectKey = Symbol() as InjectionKey<typeof routeFn>;

/**
 * Injects the Ziggy route helper provided from the global function
 */
export function useRoute() {
	return inject(injectKey)!;
}
