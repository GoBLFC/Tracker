<template>
	<ToggleSwitch v-model="checked" :disabled="loading">
		<template #handle v-if="loading">
			<FontAwesomeIcon :icon="faCircleNotch" class="text-primary" spin />
		</template>
	</ToggleSwitch>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Errors } from '@inertiajs/core';
import { useAppSettings } from '@/lib/settings';
import { useToast } from '@/lib/toast';
import { useRoute } from '@/lib/route';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch } from '@fortawesome/free-solid-svg-icons';

const emit = defineEmits<{
	(e: 'changing', authorizing: boolean): void;
	(e: 'change', authorized: boolean): void;
	(e: 'error', errors: Errors): void;
}>();

const route = useRoute();
const { isKiosk } = useAppSettings();
const toast = useToast();

const checked = ref(isKiosk.value);
const loading = ref(false);

watch(checked, (authorize) => {
	router.post(
		route(`kiosks.${authorize ? '' : 'de'}authorize.post`),
		{},
		{
			replace: true,
			preserveScroll: true,
			preserveState: true,
			only: ['isKiosk', 'flash'],

			onStart() {
				loading.value = true;
				emit('changing', authorize);
			},
			onSuccess() {
				emit('change', authorize);
			},
			onError(errors) {
				checked.value = !authorize;
				emit('error', errors);

				const msg = `Failed to ${authorize ? '' : 'de'}authorize kiosk.`;
				console.error(msg, errors);
				toast.error(msg);
			},
			onFinish() {
				loading.value = false;
			},
		},
	);
});
</script>
