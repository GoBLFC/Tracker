<template>
	<DataTable
		v-if="!skeleton"
		:value="values"
		data-key="id"
		lazy
		:loading="request.processing.value"
		paginator
		:rows
		:rows-per-page-options="[5, 10, 15, 20, 50]"
		sortable
		sort-field="display_name"
		:sort-order="1"
		v-model:filters="filters"
		filter-display="row"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
		@page="loadPage"
		@sort="loadPage"
		@filter="loadPage"
	>
		<Column
			field="badge_id"
			header="ID"
			sortable
			data-type="number"
			:show-filter-menu="false"
		>
			<template #filter="{ filterModel, filterCallback }">
				<div class="flex">
					<InputText
						v-model="filterModel.value"
						v-debounce="filterCallback"
						inputmode="numeric"
						class="grow w-24"
					/>
				</div>
			</template>
		</Column>

		<Column
			field="display_name"
			header="Name"
			sortable
			:show-filter-menu="false"
		>
			<template #body="{ data: user }: { data: User }">
				<VolunteerName :volunteer="user" />
			</template>

			<template #filter="{ filterModel, filterCallback }">
				<div class="flex">
					<InputText
						v-model="filterModel.value"
						v-debounce="filterCallback"
						type="text"
						class="grow w-32"
					/>
				</div>
			</template>
		</Column>

		<Column
			field="role"
			header="Role"
			sortable
			data-type="number"
			:show-filter-menu="false"
			class="w-76"
		>
			<template #body="{ data: user }: { data: User }">
				<UserRoleSelector :user />
			</template>

			<template #filter="{ filterModel, filterCallback }">
				<div class="flex">
					<Select
						v-model="filterModel.value"
						:options="roleOptions"
						option-label="name"
						option-value="id"
						placeholder="Any"
						show-clear
						class="grow"
						@change="filterCallback"
					/>
				</div>
			</template>
		</Column>

		<Column
			v-if="actions && isAdmin"
			header="Actions"
			class="text-end"
			:pt="{ columnHeaderContent: { class: 'justify-end' } }"
		>
			<template #body="{ data: user }: { data: User }">
				<UserActionButtons :user />
			</template>
		</Column>

		<template #empty>
			<slot name="empty">
				<p>There aren't any matching users.</p>
			</slot>
		</template>
	</DataTable>

	<SkeletonTable
		v-else
		:columns="
			actions && isAdmin
				? ['ID', 'Name', 'Role', 'Actions']
				: ['ID', 'Name', 'Role']
		"
	/>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import vueDebounce from 'vue-debounce';
import { FilterMatchMode } from '@primevue/core/api';
import type { DataTableFilterEvent, DataTablePageEvent, DataTableSortEvent } from 'primevue/datatable';

import { useInertiaRequest } from '@/lib/request';
import { roleNames, useUser } from '@/lib/user';
import User from '@/data/impl/User';
import type RawUser from '@/data/User';

import VolunteerName from '../Volunteer/VolunteerName.vue';
import UserActionButtons from './UserActionButtons.vue';
import SkeletonTable from '../Common/SkeletonTable.vue';
import UserRoleSelector from './UserRoleSelector.vue';

const {
	users,
	rows = 20,
	filterValues = {},
	actions = true,
	skeleton = false,
} = defineProps<{
	users?: RawUser[];
	total?: number;
	rows?: number;
	filterValues: {
		badge_id?: number | null;
		name?: string | null;
		role?: number | null;
	};
	actions?: boolean;
	skeleton?: boolean;
}>();

const vDebounce = vueDebounce({ lock: true, defaultTime: '400ms' });
const request = useInertiaRequest();
const { isAdmin } = useUser();

const values = computed(() => (users ? User.load(users) : undefined));

// Filtering
const filters = ref({
	badge_id: {
		value: filterValues.badge_id,
		matchMode: FilterMatchMode.EQUALS,
	},
	display_name: {
		value: filterValues.name,
		matchMode: FilterMatchMode.CONTAINS,
	},
	role: { value: filterValues.role, matchMode: FilterMatchMode.EQUALS },
});
const roleOptions = Object.entries(roleNames)
	.map(([id, name]) => ({
		id: Number(id),
		name,
	}))
	.sort((a, b) => b.id - a.id);

watch(
	() => filterValues,
	(newValues) => {
		if (!newValues) return;
		filters.value.badge_id.value = newValues.badge_id;
		filters.value.display_name.value = newValues.name;
		filters.value.role.value = newValues.role;
	},
);

/**
 * Loads a page of user results using the current filters and sorting data
 */
async function loadPage(evt: DataTablePageEvent | DataTableSortEvent | DataTableFilterEvent) {
	request.get('users.index', {
		page: 'page' in evt && evt.page !== 0 ? evt.page + 1 : undefined,
		count: evt.rows !== 20 ? evt.rows : undefined,
		sortBy: evt.sortField !== 'display_name' ? (evt.sortField as string) : undefined,
		sortDir:
			evt.sortField !== 'display_name' || evt.sortOrder !== 1
				? evt.sortOrder === -1
					? 'desc'
					: 'asc'
				: undefined,
		badge_id: filters.value.badge_id.value || undefined,
		name: filters.value.display_name.value || undefined,
		role: filters.value.role.value ?? undefined,
	});
}
</script>
