<template>
	<ButtonGroup aria-label="Time entry actions">
		<IconButton
			v-if="!entry.stop"
			variant="text"
			size="small"
			severity="warn"
			:icon="faArrowRightFromBracket"
			:loading="request.processing.value"
			:disabled="deleted || request.processing.value"
			v-tooltip.bottom="'Check Out'"
			@click="checkout"
		/>

		<IconButton
			variant="text"
			size="small"
			severity="danger"
			:icon="faTrash"
			:loading="request.processing.value"
			:disabled="deleted || request.processing.value"
			v-tooltip.bottom="'Delete'"
			@click="del"
		/>
	</ButtonGroup>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useConfirm } from '@/lib/confirm';
import { useRequest } from '@/lib/request';
import type TimeEntry from '@/data/impl/TimeEntry';
import type RawTimeEntry from '@/data/TimeEntry';

import { faTrash, faArrowRightFromBracket } from '@fortawesome/free-solid-svg-icons';
import IconButton from '../Common/IconButton.vue';

const { entry } = defineProps<{ entry: RawTimeEntry | TimeEntry }>();
const emit = defineEmits<{
	(e: 'checkout', entry: RawTimeEntry): void;
	(e: 'delete'): void;
}>();

const { confirm } = useConfirm();
const request = useRequest();

const deleted = ref(false);
watch(
	() => entry,
	() => {
		deleted.value = false;
	},
);

/**
 * Sends a request to end the time entry and emits the checkout event with the updated data if successful
 */
async function checkout() {
	const confirmed = await confirm('Check out volunteer?', {
		accept: { label: 'Check Out', severity: 'warn' },
	});
	if (!confirmed) return;

	const { time_entry: newEntry } = await request.post<{
		time_entry: RawTimeEntry;
	}>(['tracker.time.checkout.post', entry.id]);
	emit('checkout', newEntry);
}

/**
 * Sends a request to delete the time entry and emits the delete event if successful
 */
async function del() {
	const confirmed = await confirm('Delete time entry?', {
		accept: { label: 'Delete', severity: 'danger' },
	});
	if (!confirmed) return;

	await request.del(['tracker.time.destroy', entry.id]);
	deleted.value = true;
	emit('delete');
}
</script>
