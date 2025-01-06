import { onMounted, onUnmounted, computed, watch, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useRoute } from './route';
import { useAppSettings } from './settings';
import { clockDuration, useNow } from './time';

export function useAutoLogout({ after, autoReset = true }: { after?: number; autoReset?: boolean } = {}) {
	const route = useRoute();
	const { isKiosk, isDevMode } = useAppSettings();
	const { now, startTicking, stopTicking } = useNow();

	const resets = ref(0);
	const currentPath = ref(window.location.pathname);

	const logoutAfter = computed(() => after ?? (isKiosk.value ? (isDevMode.value ? 3600e3 : 60e3) : 0));
	const logoutAt = computed(() =>
		resets.value >= 0 && logoutAfter.value > 0 && logoutAfter.value < Number.POSITIVE_INFINITY
			? new Date(Date.now() + logoutAfter.value + 500)
			: null,
	);

	const timeLeft = computed(() => (logoutAt.value ? logoutAt.value.getTime() - now.value! : logoutAfter.value));
	const countdown = computed(() => (timeLeft.value > 1e3 ? clockDuration(timeLeft.value) : 'Goodbye!'));

	let logoutTimeout: ReturnType<typeof setTimeout> | null = null;

	// Set up and tear down the auto logout timer/countdown as needed
	onMounted(setup);
	onUnmounted(tearDown);
	watch(logoutAt, () => {
		tearDown();
		setup();
	});

	// Reset the countdown when changing pages or making requests
	if (autoReset) {
		onUnmounted(
			router.on('navigate', (evt) => {
				currentPath.value = evt.detail.page.url;
				reset();
			}),
		);

		onUnmounted(
			router.on('finish', (evt) => {
				if (evt.detail.visit.url.pathname !== currentPath.value) reset();
			}),
		);
	}

	/**
	 * Sends a request to log the user out
	 */
	function logout() {
		router.get(route('auth.logout'));
	}

	/**
	 * Sets up the auto-logout timer and countdown
	 */
	function setup() {
		if (!logoutAt.value) return;

		logoutTimeout = setTimeout(logout, logoutAfter.value + 1e3);
		startTicking();

		console.debug(`Set up auto-logout for ${logoutAfter.value / 1e3}s (${logoutAt.value})`);
	}

	/**
	 * Tears down the auto-logout timer and countdown
	 */
	function tearDown() {
		stopTicking();
		if (logoutTimeout) clearTimeout(logoutTimeout);
	}

	/**
	 * Resets the logout timer
	 */
	function reset() {
		resets.value++;
	}

	return {
		logoutAt,
		logoutAfter,
		timeLeft,
		countdown,
		logout,
		reset,
	};
}
