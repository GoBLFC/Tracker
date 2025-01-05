<template>
	<DataTable
		v-if="!loading"
		:value="attendees"
		data-key="id"
		paginator
		:rows="10"
		:rows-per-page-options="[5, 10, 15, 20, 50]"
		sortable
		:sort-field="gatekeeper ? 'badge_name' : 'pivot.created_at'"
		:sort-order="1"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
	>
		<Column field="badge_id" header="ID" sortable data-type="number" />

		<Column field="badge_name" header="Badge Name" sortable />

		<Column
			v-if="!gatekeeper"
			field="pivot.created_at"
			header="Logged"
			sortable
			data-type="date"
		>
			<template #body="{ data: attendee }: { data: Attendee }">
				<DateTime :date="attendee.pivot.created_at" />
			</template>
		</Column>

		<Column
			v-if="!readOnly && (!gatekeeper || isManager)"
			header="Actions"
			class="text-end"
			:pt="{ columnHeaderContent: { class: 'justify-end' } }"
		>
			<template #body="{ data: attendee }: { data: Attendee }">
				<AttendeeActionButtons :attendee :attendee-log />
			</template>
		</Column>

		<template #empty>
			<slot name="empty">
				<p>
					There aren't any
					{{ gatekeeper ? "gatekeepers" : "attendees" }} yet.
				</p>
			</slot>
		</template>
	</DataTable>

	<SkeletonTable v-else :columns="['ID', 'Name', 'Logged', 'Actions']" />
</template>

<script setup lang="ts">
import { useUser } from '@/lib/user';
import type AttendeeLog from '@/data/AttendeeLog';
import type Attendee from '@/data/Attendee';

import AttendeeActionButtons from './AttendeeActionButtons.vue';
import SkeletonTable from '../Common/SkeletonTable.vue';
import DateTime from '../Common/DateTime.vue';

const {
	attendees,
	gatekeeper = false,
	loading = false,
	readOnly = false,
} = defineProps<{
	attendeeLog: AttendeeLog;
	attendees?: Attendee[];
	gatekeeper?: boolean;
	loading?: boolean;
	readOnly?: boolean;
}>();

const { isManager } = useUser();
</script>
