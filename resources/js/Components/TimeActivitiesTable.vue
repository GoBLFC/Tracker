<template>
	<DataTable
		:value="activities"
		paginator
		:rows="10"
		:rows-per-page-options="[5, 10, 20]"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
	>
		<Column header="ID">
			<template #body="{ data: activity }: { data: TimeEntryActivity }">
				{{ activity.subject.user.badge_id }}
			</template>
		</Column>

		<Column header="Badge Name">
			<template #body="{ data: activity }: { data: TimeEntryActivity }">
				{{ activity.subject.user.badge_name }}
			</template>
		</Column>

		<Column header="Action Taken">
			<template #body="{ data: activity }: { data: TimeEntryActivity }">
				<ShiftStatusChip
					:checked-in="!activity.properties.attributes.stop"
				/>
			</template>
		</Column>

		<Column header="Time">
			<template #body="{ data: activity }: { data: TimeEntryActivity }">
				{{ isoToDateTimeString(activity.subject.start) }}
			</template>
		</Column>

		<Column header="Duration">
			<template #body="{ data: activity }: { data: TimeEntryActivity }">
				<Duration
					:start="activity.subject.start"
					:stop="activity.subject.stop"
					:now
				/>
			</template>
		</Column>

		<Column
			header="Actions"
			class="text-end"
			:pt="{ columnHeaderContent: { class: 'justify-end' } }"
		>
			<template #body="{ data: activity }: { data: TimeEntryActivity }">
				<Button
					variant="link"
					size="small"
					class="p-1"
					aria-label="View Volunteer"
					v-tooltip.left="'View Volunteer'"
					@click="emit('select', activity.subject.user.id)"
				>
					<template #icon>
						<FontAwesomeIcon :icon="faMagnifyingGlass" />
					</template>
				</Button>
			</template>
		</Column>

		<template #empty>
			<slot name="empty">
				<p>There aren't any time activities.</p>
			</slot>
		</template>
	</DataTable>
</template>

<script setup lang="ts">
import { useTime } from '../lib/time';
import type TimeEntryActivity from '../data/TimeEntryActivity';
import type { UserId } from '../data/User';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faMagnifyingGlass } from '@fortawesome/free-solid-svg-icons';
import ShiftStatusChip from './ShiftStatusChip.vue';
import Duration from './Duration.vue';

defineProps<{
	activities: TimeEntryActivity[];
	now?: number;
}>();
const emit = defineEmits<(e: 'select', userId: UserId) => void>();

const { isoToDateTimeString } = useTime();
</script>
