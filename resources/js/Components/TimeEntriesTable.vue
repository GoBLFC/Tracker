<template>
	<DataTable
		v-if="entries"
		:value="values"
		data-key="id"
		paginator
		:rows="10"
		:rows-per-page-options="[5, 10, 15, 20]"
		sortable
		sort-field="start"
		:sort-order="1"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
	>
		<Column field="user.badge_id" header="ID" sortable data-type="number" />

		<Column field="user.display_name" header="Name" sortable>
			<template #body="{ data: entry }: { data: TimeEntry }">
				<VolunteerName :volunteer="entry.user!" />
			</template>
		</Column>

		<Column field="department.name" header="Department" sortable />

		<Column field="start" header="Start Time" sortable data-type="date">
			<template #body="{ data: entry }: { data: TimeEntry }">
				<DateTime :date="entry.start" />
			</template>
		</Column>

		<Column field="duration" header="Duration" sortable data-type="number">
			<template #body="{ data: entry }: { data: TimeEntry }">
				<Duration :start="entry.start" :stop="entry.stop" :now />
			</template>
		</Column>

		<Column
			header="Actions"
			class="text-end"
			:pt="{ columnHeaderContent: { class: 'justify-end' } }"
		>
			<template #body="{ data: entry }: { data: TimeEntry }">
				<VolunteerViewButton @click="emit('select', entry.user!.id)" />
			</template>
		</Column>

		<template #empty>
			<slot name="empty">
				<p>There aren't any time entries.</p>
			</slot>
		</template>
	</DataTable>

	<SkeletonTable
		v-else
		:columns="[
			'ID',
			'Name',
			'Department',
			'Start Time',
			'Duration',
			'Actions',
		]"
	/>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import TimeEntry from '../data/impl/TimeEntry';
import type RawTimeEntry from '../data/TimeEntry';
import type { UserId } from '../data/User';

import SkeletonTable from './SkeletonTable.vue';
import VolunteerName from './VolunteerName.vue';
import VolunteerViewButton from './VolunteerViewButton.vue';
import DateTime from './DateTime.vue';
import Duration from './Duration.vue';

const { entries } = defineProps<{
	entries?: RawTimeEntry[];
	now?: number;
}>();
const emit = defineEmits<(e: 'select', userId: UserId) => void>();

const values = computed(() => (entries ? TimeEntry.load(entries) : undefined));
</script>
