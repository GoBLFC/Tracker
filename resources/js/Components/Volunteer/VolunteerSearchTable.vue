<template>
	<DataTable
		v-if="!skeleton"
		:value="users"
		data-key="id"
		selection-mode="single"
		paginator
		:rows="10"
		:rows-per-page-options="[5, 10, 15, 20]"
		sortable
		:removable-sort="true"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
		@row-click="(evt) => emit('select', evt.data.id)"
	>
		<Column field="badge_id" header="ID" sortable />

		<Column field="display_name" header="Name" sortable>
			<template #body="{ data: user }: { data: User }">
				<VolunteerName
					:volunteer="user"
					:event
					@click.prevent="emit('select', user.id)"
				/>
			</template>
		</Column>

		<Column header="Status">
			<template #body="{ data: user }: { data: User }">
				<div class="flex flex-wrap gap-2">
					<ShiftStatusTag
						:checked-in="Boolean(getDepartment(user))"
						:department="getDepartment(user)"
					/>

					<ResponsiveTag
						v-if="user.role === -2"
						label="Banned"
						severity="danger"
						:icon="faUserSlash"
					/>
				</div>
			</template>
		</Column>

		<template #header>
			<IconField class="sm:w-96">
				<InputIcon>
					<FontAwesomeIcon
						:icon="
							request.processing.value
								? faCircleNotch
								: faMagnifyingGlass
						"
						:spin="request.processing.value"
					/>
				</InputIcon>
				<InputText
					v-model="query"
					fluid
					placeholder="Search volunteers"
					aria-label="Search volunteers"
					v-debounce:400ms="searchUsers"
				/>
			</IconField>
		</template>

		<template #empty>
			<p v-if="request.processing.value">Searching&hellip;</p>
			<p v-else-if="debouncedQuery">No volunteers found.</p>
			<p v-else>
				Enter a badge number, name, or username above to search for
				matching volunteers.
			</p>
		</template>
	</DataTable>

	<SkeletonTable v-else :columns="['ID', 'Name', 'Status']">
		<template #header>
			<IconField class="sm:w-96">
				<InputIcon>
					<FontAwesomeIcon
						:icon="
							request.processing.value
								? faCircleNotch
								: faMagnifyingGlass
						"
						:spin="request.processing.value"
					/>
				</InputIcon>
				<InputText
					v-model="query"
					fluid
					placeholder="Search volunteers"
					aria-label="Search volunteers"
					disabled
				/>
			</IconField>
		</template>
	</SkeletonTable>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import vueDebounce from 'vue-debounce';
import { useRequest } from '@/lib/request';
import User from '@/data/impl/User';
import type RawUser from '@/data/User';
import type { UserId } from '@/data/User';
import type Department from '@/data/Department';
import type Event from '@/data/Event';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faMagnifyingGlass, faUserSlash, faCircleNotch } from '@fortawesome/free-solid-svg-icons';
import ShiftStatusTag from '../TimeEntry/ShiftStatusTag.vue';
import VolunteerName from './VolunteerName.vue';
import ResponsiveTag from '../Common/ResponsiveTag.vue';
import SkeletonTable from '../Common/SkeletonTable.vue';

const { skeleton = false } = defineProps<{
	event?: Event | null;
	skeleton?: boolean;
}>();
const emit = defineEmits<(e: 'select', userId: UserId) => void>();

const vDebounce = vueDebounce({ lock: true });
const request = useRequest();
const query = ref('');
const debouncedQuery = ref('');
const users = ref<User[] | null>(null);

/**
 * Sends a request to search for users that match the input query
 */
async function searchUsers() {
	debouncedQuery.value = query.value;

	if (!query.value.trim()) {
		users.value = null;
		return;
	}

	const searchingFor = query.value;
	const { users: resultUsers } = await request.get<{ users: RawUser[] }>('users.search', {
		q: query.value,
	});

	if (resultUsers && searchingFor === query.value) users.value = User.load(resultUsers);
}

/**
 * Gets the department name for a user's current time entry, if there is one
 */
function getDepartment(user: User): Department | undefined {
	return user.time_entries?.[0]?.department;
}
</script>
