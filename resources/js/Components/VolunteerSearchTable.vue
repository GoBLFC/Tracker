<template>
	<DataTable
		:value="users"
		paginator
		:rows="10"
		:rows-per-page-options="[5, 10, 20]"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
	>
		<Column header="ID" field="badge_id" />

		<Column header="Name" field="badge_name" />

		<Column header="Status">
			<template #body="{ data: user }: { data: User }">
				<div class="flex flex-wrap gap-1">
					<ShiftStatusChip
						:checked-in="Boolean(getDepartment(user))"
						:department="getDepartment(user)"
					/>

					<Chip
						v-if="user.role === -2"
						class="py-1"
						v-tooltip.left="'Banned'"
					>
						<FontAwesomeIcon
							:icon="faUserSlash"
							class="text-red-500"
						/>
						<span class="sr-only md:not-sr-only">Banned</span>
					</Chip>
				</div>
			</template>
		</Column>

		<Column
			header="Actions"
			class="text-end"
			:pt="{ columnHeaderContent: { class: 'justify-end' } }"
		>
			<template #body="{ data: user }: { data: User }">
				<Button
					variant="link"
					size="small"
					class="p-1"
					aria-label="View Volunteer"
					v-tooltip.left="'View Volunteer'"
					@click="emit('select', user.id)"
				>
					<template #icon>
						<FontAwesomeIcon :icon="faMagnifyingGlass" />
					</template>
				</Button>
			</template>
		</Column>

		<template #header>
			<div class="flex justify-between">
				<IconField class="sm:w-96">
					<InputIcon>
						<FontAwesomeIcon :icon="faMagnifyingGlass" />
					</InputIcon>
					<InputText
						v-model="query"
						class="w-full"
						placeholder="Search volunteers"
						aria-label="Search volunteers"
						v-debounce:400ms="searchUsers"
					/>
				</IconField>

				<Button severity="secondary" text v-tooltip.top="'Create User'">
					<template #icon>
						<FontAwesomeIcon :icon="faUserPlus" />
						<span class="sr-only">Create User</span>
					</template>
				</Button>
			</div>
		</template>

		<template #empty>
			<p v-if="query">No volunteers found.</p>
			<p v-else>
				Enter a badge number, name, or username above to search for
				matching volunteers.
			</p>
		</template>
	</DataTable>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import vueDebounce from 'vue-debounce';
import { useRequest } from '../lib/request';
import type User from '../data/User';
import type { UserId } from '../data/User';
import type Department from '../data/Department';

import { faUserPlus, faMagnifyingGlass, faUserSlash } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import ShiftStatusChip from './ShiftStatusChip.vue';

const emit = defineEmits<(e: 'select', userId: UserId) => void>();

const vDebounce = vueDebounce({ lock: true });
const request = useRequest();
const query = ref('');
const users = ref<User[] | null>(null);

/**
 * Sends a request to search for users that match the input query
 */
async function searchUsers() {
	if (!query.value.trim()) {
		users.value = null;
		return;
	}

	const { users: resultUsers } = await request.get<{ users: User[] }>('users.search', {
		q: query.value,
	});
	if (resultUsers) users.value = resultUsers;
}

/**
 * Gets the department name for a user's current time entry, if there is one
 */
function getDepartment(user: User): Department | undefined {
	return user.time_entries?.[0]?.department;
}
</script>
