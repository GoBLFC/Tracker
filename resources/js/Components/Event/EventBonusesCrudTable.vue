<template>
	<CrudTable
		entity-name="bonus"
		entity-plural="bonuses"
		route-slug="bonuses"
		:create-route="['events.bonuses.store', event!.id]"
		:fields="[
					{ key: 'start', label: 'Start', type: 'datetime', required: true },
					{ key: 'stop', label: 'Stop', type: 'datetime', required: true },
					{
						key: 'modifier',
						label: 'Multiplier',
						type: 'number',
						required: true,
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
						required: true,
						multiple: true,
						options: departments?.map(dept => ({ label: dept.name, value: dept.id })) ?? [],
						display: data => data
							?.map(id => departments?.find(dept => dept.id === id)?.name ?? id)
							?.join(', ') ?? '',
					},
				]"
		:readonly
		:items="bonuses ?? []"
		:table-props="{ sortField: 'start' }"
		:skeleton="!bonuses"
		help-title="Bonuses"
	>
		<template #help>
			<p>
				Time Bonuses are periods of time during an event when volunteers working for a specific department get a
				multiplier applied to their time worked.
			</p>
		</template>
	</CrudTable>
</template>

<script setup lang="ts">
import type TrackerEvent from '@/data/Event';
import type TimeBonus from '@/data/TimeBonus';
import type Department from '@/data/Department';

import CrudTable from '@/Components/App/CrudTable.vue';

const { readonly = false } = defineProps<{
	event: TrackerEvent | null;
	bonuses: TimeBonus[] | null;
	departments: Department[] | null;
	readonly?: boolean;
}>();
</script>
