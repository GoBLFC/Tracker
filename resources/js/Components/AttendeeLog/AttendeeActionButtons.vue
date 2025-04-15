<template>
	<IconButton
		size="small"
		severity="danger"
		variant="text"
		:icon="faTrash"
		:loading="request.processing.value"
		v-tooltip.bottom="'Remove'"
		@click="del"
	/>
</template>

<script setup lang="ts">
import { useConfirm } from '@/lib/confirm';
import { useInertiaRequest } from '@/lib/request';
import type AttendeeLog from '@/data/AttendeeLog';
import type Attendee from '@/data/Attendee';

import { faTrash } from '@fortawesome/free-solid-svg-icons';
import IconButton from '../Common/IconButton.vue';

const { attendeeLog, attendee } = defineProps<{
	attendeeLog: AttendeeLog;
	attendee: Attendee;
}>();

const request = useInertiaRequest();
const { confirm } = useConfirm();

/**
 * Sends a request to delete the attendee from the attendee log
 */
async function del() {
	const confirmed = await confirm(`Remove ${attendee.pivot.type}?`, {
		accept: { label: 'Remove', severity: 'danger' },
	});
	if (!confirmed) return;

	request.del(['attendee-logs.users.destroy', [attendeeLog.id, attendee.pivot.type, attendee.id]], {
		only: ['attendeeLog', 'flash'],
	});
}
</script>
