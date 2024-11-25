<template>
	<DataTable
		:value="entries"
		paginator
		:rows="10"
		:rows-per-page-options="[5, 10, 20]"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
	>
		<Column header="ID">
			<template #body="{ data: entry }: { data: TimeEntry }">
				{{ entry.user.badge_id }}
			</template>
		</Column>

		<Column header="Badge Name">
			<template #body="{ data: entry }: { data: TimeEntry }">
				{{ entry.user.badge_name }}
			</template>
		</Column>

		<Column header="Department">
			<template #body="{ data: entry }: { data: TimeEntry }">
				{{ entry.department.name }}
			</template>
		</Column>

		<Column header="Start Time">
			<template #body="{ data: entry }: { data: TimeEntry }">
				{{ isoToDateTimeString(entry.start) }}
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
				<Button
					variant="link"
					class="p-0"
					size="small"
					aria-label="View Volunteer"
					v-tooltip.left="'View Volunteer'"
					@click="emit('select', entry.user.id)"
				>
					<template #icon>
						<FontAwesomeIcon :icon="faMagnifyingGlass" />
					</template>
				</Button>
			</template>
		</Column>
	</DataTable>
</template>

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faMagnifyingGlass } from '@fortawesome/free-solid-svg-icons';
import { useTime } from '../lib/time';
import type TimeEntry from '../data/TimeEntry';
import type { UserId } from '../data/User';
import Duration from './Duration.vue';

defineProps<{
	entries: TimeEntry[];
	now?: number;
}>();
const emit = defineEmits<(e: 'select', userId: UserId) => void>();

const { isoToDateTimeString } = useTime();
</script>
