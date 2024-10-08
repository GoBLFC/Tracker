<template>
	<button
		type="button"
		class="btn"
		:class="{ 'btn-warning': !isKiosk, 'btn-danger': isKiosk }"
		@click="toggle"
		:disabled="loading"
	>
		<span v-if="!loading">
			<FontAwesomeIcon
				class="me-1"
				:icon="isKiosk ? faLock : faLockOpen"
			/>
			{{ isKiosk ? "Deauthorize" : "Authorize" }} Kiosk
		</span>
		<span v-else>
			<FontAwesomeIcon class="me-1" :icon="faCircleNotch" spin />
			{{ isKiosk ? "Deauthorizing" : "Authorizing" }} Kiosk&hellip;
		</span>
	</button>
</template>

<script setup>
import { inject, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch, faLock, faLockOpen } from '@fortawesome/free-solid-svg-icons';
import { useSettings } from '../lib/settings';
import { useToast } from '../lib/toast';

const route = inject('route');
const { isKiosk } = useSettings();
const toast = useToast();

const loading = ref(false);

/**
 * Sends the appropriate request to authorize or deauthorize the kiosk based on the current state
 */
function toggle() {
	const authorized = isKiosk.value;
	router.post(
		route(`kiosks.${authorized ? 'de' : ''}authorize.post`),
		{},
		{
			replace: true,
			preserveScroll: true,
			preserveState: true,
			only: ['isKiosk', 'flash'],

			onError(errors) {
				const msg = `Failed to ${authorized ? 'de' : ''}authorize kiosk.`;
				console.error(msg, errors);
				toast.error(msg);
			},
			onStart() {
				loading.value = true;
			},
			onFinish() {
				loading.value = false;
			},
		},
	);
}
</script>
