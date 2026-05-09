<template>
	<FullContentHeightPanel header="Bonuses">
		<template #icons>
			<HelpDialogButton header="Bonuses">
				<p>
					Time Bonuses are periods of time during an event when volunteers working for a specific department
					get a multiplier applied to their time worked.
				</p>
			</HelpDialogButton>
		</template>

		<CrudTable
			entity-name="bonus"
			entity-plural="bonuses"
			route-slug="bonuses"
			:create-route="['events.bonuses.store', event!.id]"
			:fields="[
					{ key: 'start', label: 'Start', type: 'datetime' },
					{ key: 'stop', label: 'Stop', type: 'datetime' },
					{
						key: 'modifier',
						label: 'Multiplier',
						type: 'number',
						min: 1,
						max: 10,
						step: 0.25,
						fractionDigits: 2,
						default: 1,
						suffix: 'x',
						class: 'w-36'
					},
					{
						key: 'departments',
						label: 'Departments',
						type: 'select',
						multiple: true,
						options: departments.map(dept => ({ label: dept.name, value: dept.id })),
						display: data => (data as string[]).map(id => departments.find(dept => dept.id === id)!.name).join(', '),
					},
				]"
			:readonly
			:items="bonuses ?? []"
			:table-props="{ sortField: 'start' }"
			:skeleton="!bonuses"
		/>
	</FullContentHeightPanel>
</template>

<script setup lang="ts">
import type TrackerEvent from '@/data/Event';
import type TimeBonus from '@/data/TimeBonus';
import type Department from '@/data/Department';

import FullContentHeightPanel from '@/Components/Common/FullContentHeightPanel.vue';
import HelpDialogButton from '@/Components/Common/HelpDialogButton.vue';
import CrudTable from '@/Components/App/CrudTable.vue';

const { readonly = false } = defineProps<{
	event: TrackerEvent | null;
	bonuses: TimeBonus[] | null;
	departments: Department[];
	readonly?: boolean;
}>();
</script>
