<template>
	<InputGroup v-if="picking">
		<Select
			v-model="role"
			:options="roleOptions"
			option-label="name"
			option-value="id"
			:disabled="request.processing.value"
		/>

		<IconButton
			severity="secondary"
			:icon="faCancel"
			:disabled="request.processing.value"
			v-tooltip.bottom="'Cancel'"
			@click="
				picking = false;
				role = user.role;
			"
		/>
		<IconButton
			severity="warn"
			:icon="faUserCheck"
			:loading="request.processing.value"
			v-tooltip.bottom="'Assign Role'"
			@click="save"
		/>
	</InputGroup>

	<div v-else class="flex items-center gap-1">
		<IconButton
			variant="text"
			severity="primary"
			size="small"
			:icon="faPencil"
			v-tooltip.bottom="'Change Role'"
			@click="picking = true"
		/>

		{{ roleNames[role] }}
	</div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

import { useInertiaRequest } from '@/lib/request';
import { roleNames } from '@/lib/user';
import type User from '@/data/impl/User';
import type RawUser from '@/data/User';

import IconButton from '../Common/IconButton.vue';
import { faCancel, faPencil, faUserCheck } from '@fortawesome/free-solid-svg-icons';

const { user } = defineProps<{ user: User | RawUser }>();

const request = useInertiaRequest();

const picking = ref(false);
const role = ref(user.role);
const roleOptions = Object.entries(roleNames)
	.map(([id, name]) => ({
		id: Number(id),
		name,
	}))
	.sort((a, b) => b.id - a.id);

watch(
	() => user,
	() => {
		role.value = user.role;
	},
);

/**
 * Sends a request to update the user's role
 */
function save() {
	request.patch(['users.update', user.id], { role: role.value });
}
</script>
