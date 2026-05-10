<template>
	<FullContentHeightPanel header="Departments">
		<template #icons>
			<HelpDialogButton header="Departments">
				<p>
					Departments are the section of your organization that deal with a specific set of tasks or goals
					before, during, and after your event. Users choose what department to check in to for their shifts,
					and so every time entry always has one associated with it.
				</p>

				<p class="mt-4">Deleting departments does <strong>not</strong> delete their associated time entries.</p>
			</HelpDialogButton>
		</template>

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
		/>
	</FullContentHeightPanel>
</template>

<script setup lang="ts">
import type TrackerEvent from '@/data/Event';
import type Department from '@/data/Department';

import FullContentHeightPanel from '@/Components/Common/FullContentHeightPanel.vue';
import HelpDialogButton from '@/Components/Common/HelpDialogButton.vue';
import CrudTable from '@/Components/App/CrudTable.vue';

const { readonly = false } = defineProps<{
	event: TrackerEvent | null;
	departments: Department[] | null;
	readonly?: boolean;
}>();
</script>
