<template>
	<ButtonGroup aria-label="Time entry actions">
		<Button
			v-if="!entry.stop"
			size="small"
			severity="warn"
			:loading="request.processing.value"
			:disabled="deleted || request.processing.value"
			v-tooltip.bottom="'Check Out'"
			@click="checkout"
		>
			<template #icon>
				<FontAwesomeIcon :icon="faArrowRightFromBracket" />
			</template>

			<template #loadingicon>
				<FontAwesomeIcon :icon="faCircleNotch" spin />
			</template>
		</Button>

		<Button
			size="small"
			severity="danger"
			:loading="request.processing.value"
			:disabled="deleted || request.processing.value"
			v-tooltip.bottom="'Delete'"
			@click="del"
		>
			<template #icon>
				<FontAwesomeIcon :icon="faTrash" />
			</template>

			<template #loadingicon>
				<FontAwesomeIcon :icon="faCircleNotch" spin />
			</template>
		</Button>
	</ButtonGroup>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useConfirm } from '../lib/confirm';
import { useRequest } from '../lib/request';
import type TimeEntry from '../data/TimeEntry';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch, faTrash, faArrowRightFromBracket } from '@fortawesome/free-solid-svg-icons';

const { entry } = defineProps<{ entry: TimeEntry }>();
const emit = defineEmits<{
	(e: 'checkout', entry: TimeEntry): void;
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
		time_entry: TimeEntry;
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
