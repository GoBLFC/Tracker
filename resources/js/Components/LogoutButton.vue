<template>
	<Link
		to="auth.logout.post"
		method="post"
		as="button"
		type="button"
		class="btn btn-danger btn-sm"
	>
		Logout
		<span v-if="logoutAt">({{ countdown }})</span>
	</Link>
</template>

<script setup>
import { onMounted, onUnmounted, ref, inject, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useSettings } from '../lib/settings';
import { clockDuration } from '../legacy/shared';
import Link from './Link.vue';

defineExpose({ logout });
const { auto, resetOnNavigate } = defineProps({
	auto: {
		type: Number,
		required: false,
	},
	resetOnNavigate: {
		type: Boolean,
		default: true,
	},
});

const route = inject('route');
const { isKiosk, isDevMode } = useSettings();

//
// Auto-logout
//
let logoutTimeout = null;
let countdownInterval = null;
const logoutAt = ref(null);
const now = ref(null);
const countdown = computed(() => {
	const timeDiff = logoutAt.value.getTime() - now.value;
	return timeDiff > 1000 ? clockDuration(timeDiff) : 'Goodbye!';
});

// Set up and tear down the auto logout timer/countdown as needed
onMounted(setupAutoLogout);
onUnmounted(tearDownAutoLogout);
watch(
	() => auto,
	() => {
		tearDownAutoLogout();
		setupAutoLogout();
	},
);

// Reset the countdown when changing pages
onUnmounted(
	router.on('finish', () => {
		if (!resetOnNavigate) return;
		tearDownAutoLogout();
		setupAutoLogout();
	}),
);

/**
 * Sends a request to log the user out
 */
function logout() {
	router.post(route('auth.logout.post'));
}

/**
 * Sets up the auto-logout timer and countdown
 */
function setupAutoLogout() {
	const logoutTime = auto ?? (isKiosk.value ? (isDevMode.value ? 3600 : 60) : 0);
	if (logoutTime <= 0 || logoutTime >= Number.POSITIVE_INFINITY) return;

	logoutAt.value = new Date(Date.now() + logoutTime * 1000 + 500);
	now.value = Date.now();
	countdownInterval = setInterval(updateCountdown, 1000);
	logoutTimeout = setTimeout(logout, logoutTime * 1000 + 500);

	console.debug(`Set up auto-logout for ${logoutTime}s (${logoutAt.value})`);
}

/**
 * Tears down the auto-logout timer and countdown
 */
function tearDownAutoLogout() {
	if (logoutTimeout) clearTimeout(logoutTimeout);
	if (countdownInterval) clearInterval(countdownInterval);
	logoutAt.value = null;
	now.value = null;
	console.debug('Tore down auto-logout');
}

/**
 * Updates the displayed countdown
 */
function updateCountdown() {
	now.value = Date.now();
}
</script>
