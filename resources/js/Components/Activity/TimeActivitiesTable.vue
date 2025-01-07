<template>
	<DataTable
		v-if="!skeleton"
		:value="values"
		data-key="id"
		selection-mode="single"
		paginator
		:rows="10"
		:rows-per-page-options="[5, 10, 15, 20]"
		sortable
		sort-field="subject.start"
		:sort-order="-1"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
		@row-click="(evt) => emit('select', evt.data.subject.user_id)"
	>
		<Column
			field="subject.user.badge_id"
			header="ID"
			sortable
			data-type="number"
		/>

		<Column field="subject.user.display_name" sortable header="Name">
			<template #body="{ data: activity }: { data: TimeEntryActivity }">
				<VolunteerName
					:volunteer="activity.subject.user!"
					:event="activity.subject.event_id"
					@click.prevent="emit('select', activity.subject.user_id)"
				/>
			</template>
		</Column>

		<Column
			field="checked_in"
			header="Action Taken"
			sortable
			data-type="boolean"
		>
			<template #body="{ data: activity }: { data: TimeEntryActivity }">
				<ShiftStatusTag :checked-in="activity.checked_in" />
			</template>
		</Column>

		<Column field="subject.start" header="Time" sortable data-type="date">
			<template #body="{ data: activity }: { data: TimeEntryActivity }">
				<DateTime :date="activity.subject.start" />
			</template>
		</Column>

		<Column
			field="subject.duration"
			header="Duration"
			sortable
			data-type="number"
		>
			<template #body="{ data: activity }: { data: TimeEntryActivity }">
				<Duration
					:start="activity.subject.start"
					:stop="activity.subject.stop"
					:now
				/>
			</template>
		</Column>

		<template #empty>
			<slot name="empty">
				<p>There aren't any time activities.</p>
			</slot>
		</template>
	</DataTable>

	<SkeletonTable
		v-else
		:columns="['ID', 'Name', 'Action Taken', 'Time', 'Duration']"
	/>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import TimeEntryActivity from '@/data/impl/TimeEntryActivity';
import type RawTimeEntryActivity from '@/data/TimeEntryActivity';
import type { UserId } from '@/data/User';

import ShiftStatusTag from '../TimeEntry/ShiftStatusTag.vue';
import VolunteerName from '../Volunteer/VolunteerName.vue';
import DateTime from '../Common/DateTime.vue';
import Duration from '../Common/Duration.vue';
import SkeletonTable from '../Common/SkeletonTable.vue';

const { activities, skeleton = false } = defineProps<{
	activities?: RawTimeEntryActivity[];
	now?: number;
	skeleton?: boolean;
}>();
const emit = defineEmits<(e: 'select', userId: UserId) => void>();

const values = computed(() => (activities ? TimeEntryActivity.load(activities) : undefined));
</script>
