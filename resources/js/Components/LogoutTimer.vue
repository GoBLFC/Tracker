<template>
	<slot :logout-at :logout-time :time-left :countdown />
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, computed, watch, toRef, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useRoute } from '../lib/route';
import { useAppSettings } from '../lib/settings';
import { clockDuration, useNow } from '../lib/time';

defineExpose({ logout });
const { auto, resetOnNavigate = true } = defineProps<{
	auto?: number;
	resetOnNavigate?: boolean;
}>();

const route = useRoute();
const { isKiosk, isDevMode } = useAppSettings();
const { now, startTicking, stopTicking } = useNow();

//
// Auto-logout
//
let logoutTimeout: ReturnType<typeof setTimeout> | null = null;

const logoutTime = toRef(() => auto ?? (isKiosk.value ? (isDevMode.value ? 3600 : 60) : 0));
const logoutAt = computed(() => {
	if (logoutTime.value <= 0 || logoutTime.value >= Number.POSITIVE_INFINITY) return null;
	return new Date(Date.now() + logoutTime.value * 1000 + 500);
});

const timeLeft = computed(() => (logoutAt.value ? logoutAt.value.getTime() - now.value! : logoutTime.value));
const countdown = computed(() => (timeLeft.value > 1000 ? clockDuration(timeLeft.value) : 'Goodbye!'));

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

	logoutTimeout = setTimeout(logout, logoutTime.value * 1000 + 1000);
	startTicking();

	console.debug(`Set up auto-logout for ${logoutTime.value}s (${logoutAt.value})`);
}

/**
 * Tears down the auto-logout timer and countdown
 */
function tearDownAutoLogout() {
	stopTicking();
	if (logoutTimeout) clearTimeout(logoutTimeout);
}
</script>
