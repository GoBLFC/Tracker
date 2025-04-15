<template>
	<ButtonGroup aria-label="User actions">
		<IconButton
			variant="text"
			size="small"
			severity="danger"
			:icon="faTrash"
			:loading
			v-tooltip.bottom="'Delete'"
			@click="del"
		/>
	</ButtonGroup>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import { useConfirm } from '@/lib/confirm';
import { useRoute } from '@/lib/route';
import type User from '@/data/impl/User';
import type RawUser from '@/data/User';

import { faTrash } from '@fortawesome/free-solid-svg-icons';
import IconButton from '../Common/IconButton.vue';

const { user } = defineProps<{ user: RawUser | User }>();

const { confirm } = useConfirm();
const route = useRoute();

const loading = ref(false);

/**
 * Sends a request to delete the time entry
 */
async function del() {
	const confirmed = await confirm('Delete user?', {
		accept: { label: 'Delete', severity: 'danger' },
	});
	if (!confirmed) return;

	router.delete(route('users.destroy', user.id), {
		preserveState: true,
		preserveScroll: true,

		onStart() {
			loading.value = true;
		},
		onFinish() {
			loading.value = false;
		},
	});
}
</script>
