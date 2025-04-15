<template>
	<ButtonGroup aria-label="User actions">
		<IconButton
			variant="text"
			size="small"
			severity="danger"
			:icon="faTrash"
			:loading="request.processing.value"
			v-tooltip.bottom="'Delete'"
			@click="del"
		/>
	</ButtonGroup>
</template>

<script setup lang="ts">
import { useConfirm } from '@/lib/confirm';
import { useInertiaRequest } from '@/lib/request';
import type User from '@/data/impl/User';
import type RawUser from '@/data/User';

import { faTrash } from '@fortawesome/free-solid-svg-icons';
import IconButton from '../Common/IconButton.vue';

const { user } = defineProps<{ user: RawUser | User }>();

const { confirm } = useConfirm();
const request = useInertiaRequest();

/**
 * Sends a request to delete the time entry
 */
async function del() {
	const confirmed = await confirm('Delete user?', {
		accept: { label: 'Delete', severity: 'danger' },
	});
	if (!confirmed) return;

	request.del(['users.destroy', user.id]);
}
</script>
