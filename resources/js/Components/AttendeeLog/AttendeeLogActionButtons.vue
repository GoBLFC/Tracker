<template>
	<IconButton
		size="small"
		severity="danger"
		variant="text"
		:icon="faTrash"
		:loading
		:disabled="loading"
		v-tooltip.bottom="'Delete'"
		@click="del"
	/>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useRoute } from '@/lib/route';
import { useConfirm } from '@/lib/confirm';
import type AttendeeLog from '@/data/AttendeeLog';

import { faTrash } from '@fortawesome/free-solid-svg-icons';
import IconButton from '../Common/IconButton.vue';

const { attendeeLog } = defineProps<{ attendeeLog: AttendeeLog }>();

const route = useRoute();
const { confirm } = useConfirm();

const loading = ref(false);

/**
 * Sends a request to delete the attendee log
 */
async function del() {
	const confirmed = await confirm('Delete attendee log?', {
		accept: { label: 'Delete', severity: 'danger' },
	});
	if (!confirmed) return;

	router.delete(route('attendee-logs.destroy', attendeeLog.id), {
		replace: true,
		preserveState: true,
		preserveScroll: true,
		only: ['attendeeLogs', 'flash'],
		onStart() {
			loading.value = true;
		},
		onFinish() {
			loading.value = false;
		},
	});
}
</script>
