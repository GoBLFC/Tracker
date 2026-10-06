<template>
	<DataTable
		v-if="!skeleton"
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

		<Column field="badge_name" header="Badge Name" sortable>
			<template #body="{ data: attendee }: { data: Attendee }">
				{{ attendee.badge_name }}
				<template v-if="attendee.pivot.overridden_by_id">
					<Tag value="Override" severity="warn" class="text-xs ms-1" />
					<div class="text-xs text-muted-color">{{ overrideSummary(attendee) }}</div>
				</template>
			</template>
		</Column>

		<Column v-if="!gatekeeper" field="pivot.created_at" header="Logged" sortable data-type="date">
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
					{{ gatekeeper ? "gatekeepers" : "attendees" }}
					yet.
				</p>
			</slot>
		</template>
	</DataTable>

	<SkeletonTable
		v-else
		:columns="
			hasActions
				? ['ID', 'Name', 'Logged', 'Actions']
				: ['ID', 'Name', 'Logged']
		"
	/>
</template>

<script setup lang="ts">
import { toRef } from 'vue';
import { useUser } from '@/lib/user';
import type AttendeeLog from '@/data/AttendeeLog';
import type Attendee from '@/data/Attendee';
import type { Overriders } from '@/data/Attendee';

import AttendeeActionButtons from './AttendeeActionButtons.vue';
import SkeletonTable from '../Common/SkeletonTable.vue';
import DateTime from '../Common/DateTime.vue';

const {
	attendees,
	gatekeeper = false,
	readOnly = false,
	skeleton = false,
	overriders,
} = defineProps<{
	attendeeLog: AttendeeLog;
	attendees?: Attendee[];
	gatekeeper?: boolean;
	readOnly?: boolean;
	skeleton?: boolean;
	overriders?: Overriders;
}>();

const { isManager } = useUser();

/**
 * Describes who let an attendee in despite the log's entry requirements, and why. It's shown as text rather than a
 * tooltip so that it's available to keyboard, screen reader, and touch users.
 */
function overrideSummary(attendee: Attendee): string {
	const overrider = overriders?.[attendee.pivot.overridden_by_id!];
	const by = overrider
		? `Allowed by ${overrider.badge_name ?? 'Unknown'} (#${overrider.badge_id})`
		: 'Allowed by override';
	return attendee.pivot.override_reason ? `${by}: ${attendee.pivot.override_reason}` : by;
}

const hasActions = toRef(() => !readOnly && (!gatekeeper || isManager.value));
</script>
