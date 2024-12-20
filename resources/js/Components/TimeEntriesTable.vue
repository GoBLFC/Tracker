<template>
	<DataTable
		v-if="entries"
		:value="entries"
		paginator
		:rows="10"
		:rows-per-page-options="[5, 10, 15, 20]"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
	>
		<Column header="ID">
			<template #body="{ data: entry }: { data: TimeEntry }">
				{{ entry.user.badge_id }}
			</template>
		</Column>

		<Column header="Name">
			<template #body="{ data: entry }: { data: TimeEntry }">
				<VolunteerName :volunteer="entry.user" />
			</template>
		</Column>

		<Column header="Department">
			<template #body="{ data: entry }: { data: TimeEntry }">
				{{ entry.department.name }}
			</template>
		</Column>

		<Column header="Start Time">
			<template #body="{ data: entry }: { data: TimeEntry }">
				<DateTime :date="entry.start" />
			</template>
		</Column>

		<Column header="Duration">
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
				<VolunteerViewButton @click="emit('select', entry.user.id)" />
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
import type TimeEntry from '../data/TimeEntry';
import type { UserId } from '../data/User';

import SkeletonTable from './SkeletonTable.vue';
import VolunteerName from './VolunteerName.vue';
import VolunteerViewButton from './VolunteerViewButton.vue';
import DateTime from './DateTime.vue';
import Duration from './Duration.vue';

defineProps<{
	entries?: TimeEntry[];
	now?: number;
}>();
const emit = defineEmits<(e: 'select', userId: UserId) => void>();
</script>
