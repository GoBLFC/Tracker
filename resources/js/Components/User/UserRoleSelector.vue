<template>
	<InputGroup v-if="picking">
		<Select
			v-model="role"
			:options="roleOptions"
			option-label="name"
			option-value="id"
			:disabled="loading"
		/>

		<IconButton
			severity="secondary"
			:icon="faCancel"
			:disabled="loading"
			v-tooltip.bottom="'Cancel'"
			@click="
				picking = false;
				role = user.role;
			"
		/>
		<IconButton
			severity="warn"
			:icon="faUserCheck"
			:loading
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
import { router } from '@inertiajs/vue3';

import { useRoute } from '@/lib/route';
import { roleNames } from '@/lib/user';
import type User from '@/data/impl/User';
import type RawUser from '@/data/User';

import IconButton from '../Common/IconButton.vue';
import { faCancel, faPencil, faUserCheck } from '@fortawesome/free-solid-svg-icons';

const { user } = defineProps<{ user: User | RawUser }>();

const route = useRoute();

const picking = ref(false);
const loading = ref(false);
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
	router.patch(
		route('users.update', [user.id]),
		{ role: role.value },
		{
			preserveState: true,
			preserveScroll: true,

			onStart() {
				loading.value = true;
			},
			onFinish() {
				loading.value = false;
			},
		},
	);
}
</script>
