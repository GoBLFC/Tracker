import { onMounted, onUnmounted, computed, watch, toRef } from 'vue';
import { router } from '@inertiajs/vue3';
import { useRoute } from './route';
import { useAppSettings } from './settings';
import { clockDuration, useNow } from './time';

export function useAutoLogout({ after, resetOnNavigate = true }: { after?: number; resetOnNavigate?: boolean } = {}) {
	const route = useRoute();
	const { isKiosk, isDevMode } = useAppSettings();
	const { now, startTicking, stopTicking } = useNow();

	const logoutAfter = toRef(() => after ?? (isKiosk.value ? (isDevMode.value ? 3600e3 : 60e3) : 0));
	const logoutAt = computed(() => {
		if (logoutAfter.value <= 0 || logoutAfter.value >= Number.POSITIVE_INFINITY) return null;
		return new Date(Date.now() + logoutAfter.value + 500);
	});

	const timeLeft = computed(() => (logoutAt.value ? logoutAt.value.getTime() - now.value! : logoutAfter.value));
	const countdown = computed(() => (timeLeft.value > 1e3 ? clockDuration(timeLeft.value) : 'Goodbye!'));

	let logoutTimeout: ReturnType<typeof setTimeout> | null = null;

	// Set up and tear down the auto logout timer/countdown as needed
	onMounted(setupAutoLogout);
	onUnmounted(tearDownAutoLogout);
	watch(logoutAt, () => {
		tearDownAutoLogout();
		setupAutoLogout();
	});

	// Reset the countdown when changing pages
	onUnmounted(
		router.on('navigate', (evt) => {
			if (!resetOnNavigate) return;
			tearDownAutoLogout();
			setupAutoLogout();
		}),
	);

	/**
	 * Sends a request to log the user out
	 */
	function logout() {
		router.get(route('auth.logout'));
	}

	/**
	 * Sets up the auto-logout timer and countdown
	 */
	function setupAutoLogout() {
		if (!logoutAt.value) return;

		logoutTimeout = setTimeout(logout, logoutAfter.value + 1e3);
		startTicking();

		console.debug(`Set up auto-logout for ${logoutAfter.value / 1e3}s (${logoutAt.value})`);
	}

	/**
	 * Tears down the auto-logout timer and countdown
	 */
	function tearDownAutoLogout() {
		stopTicking();
		if (logoutTimeout) clearTimeout(logoutTimeout);
	}

	return {
		logoutAt,
		logoutAfter,
		timeLeft,
		countdown,
		logout,
	};
}
