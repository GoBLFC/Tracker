<template>
	<IconButton
		size="small"
		severity="danger"
		variant="text"
		:icon="faTrash"
		:loading="request.processing.value"
		v-tooltip.bottom="'Delete'"
		@click="del"
	/>
</template>

<script setup lang="ts">
import { useConfirm } from '@/lib/confirm';
import { useInertiaRequest } from '@/lib/request';
import type AttendeeLog from '@/data/AttendeeLog';

import { faTrash } from '@fortawesome/free-solid-svg-icons';
import IconButton from '../Common/IconButton.vue';

const { attendeeLog } = defineProps<{ attendeeLog: AttendeeLog }>();

const { confirm } = useConfirm();
const request = useInertiaRequest();

/**
 * Sends a request to delete the attendee log
 */
async function del() {
	const confirmed = await confirm('Delete attendee log?', {
		accept: { label: 'Delete', severity: 'danger' },
	});
	if (!confirmed) return;

	request.del(['attendee-logs.destroy', attendeeLog.id], {
		only: ['attendeeLogs', 'flash'],
	});
}
</script>
