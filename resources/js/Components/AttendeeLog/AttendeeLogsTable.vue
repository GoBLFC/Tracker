<template>
	<DataTable
		v-if="!loading"
		:value="attendeeLogs"
		data-key="id"
		selection-mode="single"
		paginator
		:rows="10"
		:rows-per-page-options="[5, 10, 15, 20]"
		sortable
		sort-field="name"
		:sort-order="1"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
		@row-click="(evt) => openLog(evt.data)"
	>
		<Column field="name" header="Name" sortable>
			<template #body="{ data: log }: { data: AttendeeLog }">
				<Link :to="['attendee-logs.show', log.id]">{{ log.name }}</Link>
			</template>
		</Column>

		<Column
			field="attendees_count"
			header="Attendees"
			sortable
			data-type="number"
		/>

		<Column
			v-if="isManager"
			field="gatekeepers_count"
			header="Gatekeepers"
			sortable
			data-type="number"
		/>

		<Column
			v-if="isAdmin"
			header="Actions"
			class="text-end"
			:pt="{ columnHeaderContent: { class: 'justify-end' } }"
		>
			<template #body="{ data: attendeeLog }: { data: AttendeeLog }">
				<AttendeeLogActionButtons :attendee-log />
			</template>
		</Column>

		<template #empty>
			<slot name="empty">
				<p>There aren't any attendee logs for this event.</p>
			</slot>
		</template>
	</DataTable>

	<SkeletonTable
		v-else
		:columns="['Name', 'Attendees', 'Gatekeepers', 'Total', 'Actions']"
	/>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useRoute } from '@/lib/route';
import { useUser } from '@/lib/user';
import type AttendeeLog from '@/data/AttendeeLog';

import AttendeeLogActionButtons from './AttendeeLogActionButtons.vue';
import SkeletonTable from '../Common/SkeletonTable.vue';
import Link from '../Common/Link.vue';

const { attendeeLogs, loading = false } = defineProps<{
	attendeeLogs?: AttendeeLog[];
	loading?: boolean;
}>();

const route = useRoute();
const { isAdmin, isManager } = useUser();

/**
 * Navigates to a specific attendee log
 */
function openLog(log: AttendeeLog) {
	router.get(route('attendee-logs.show', log.id));
}
</script>
