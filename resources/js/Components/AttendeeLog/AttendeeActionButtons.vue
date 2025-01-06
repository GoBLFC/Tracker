<template>
	<IconButton
		size="small"
		severity="danger"
		variant="text"
		:icon="faTrash"
		:loading
		:disabled="loading"
		v-tooltip.bottom="'Remove'"
		@click="del"
	/>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useRoute } from '@/lib/route';
import { useConfirm } from '@/lib/confirm';
import type AttendeeLog from '@/data/AttendeeLog';
import type Attendee from '@/data/Attendee';

import { faTrash } from '@fortawesome/free-solid-svg-icons';
import IconButton from '../Common/IconButton.vue';

const { attendeeLog, attendee } = defineProps<{
	attendeeLog: AttendeeLog;
	attendee: Attendee;
}>();

const route = useRoute();
const { confirm } = useConfirm();

const loading = ref(false);

/**
 * Sends a request to delete the attendee from the attendee log
 */
async function del() {
	const confirmed = await confirm(`Remove ${attendee.pivot.type}?`, {
		accept: { label: 'Remove', severity: 'danger' },
	});
	if (!confirmed) return;

	router.delete(route('attendee-logs.users.destroy', [attendeeLog.id, attendee.pivot.type, attendee.id]), {
		replace: true,
		preserveState: true,
		preserveScroll: true,
		only: ['attendeeLog', 'flash'],
		onStart() {
			loading.value = true;
		},
		onFinish() {
			loading.value = false;
		},
	});
}
</script>
