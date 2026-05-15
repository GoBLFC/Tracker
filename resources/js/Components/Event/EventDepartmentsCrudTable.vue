<template>
	<CrudTable
		entity-name="department"
		route-slug="departments"
		:create-route="['events.departments.store', event!.id]"
		:fields="[
					{ key: 'name', label: 'Name', required: true, max: 64 },
					{ key: 'hidden', label: 'Hide', type: 'switch', default: false },
				]"
		:readonly
		:items="departments ?? []"
		:table-props="{ sortField: 'name' }"
		:skeleton="!departments"
		help-title="Departments"
	>
		<template #help>
			<p>
				Departments are groups of people in your organization that deal with a specific set of tasks or goals
				before, during, and after your event. Users choose what department to check in to for their shifts, and
				so every time entry has one associated with it.
			</p>
			<p class="mt-4">Deleting departments does <strong>not</strong> delete their associated time entries.</p>
		</template>
	</CrudTable>
</template>

<script setup lang="ts">
import type TrackerEvent from '@/data/Event';
import type Department from '@/data/Department';

import CrudTable from '@/Components/App/CrudTable.vue';

const { readonly = false } = defineProps<{
	event: TrackerEvent | null;
	departments: Department[] | null;
	readonly?: boolean;
}>();
</script>
