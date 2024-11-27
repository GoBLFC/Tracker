<template>
	<Panel header="Shift Log">
		<DataTable :value="volunteer!.stats.entries">
			<Column header="In">
				<template #body="{ data: entry }: { data: TimeEntry }">
					{{ isoToDateTimeString(entry.start) }}
				</template>
			</Column>

			<Column header="Out">
				<template #body="{ data: entry }: { data: TimeEntry }">
					{{ entry.stop ? isoToDateTimeString(entry.stop) : "" }}
				</template>
			</Column>

			<Column header="Department">
				<template #body="{ data: entry }: { data: TimeEntry }">
					{{ entry.department.name }}
				</template>
			</Column>

			<Column header="Worked">
				<template #body="{ data: entry }: { data: TimeEntry }">
					<Duration :start="entry.start" :stop="entry.stop" :now />
				</template>
			</Column>

			<Column header="Earned">
				<template #body="{ data: entry }: { data: TimeEntry }">
					<Duration :start="entry.start" :stop="entry.stop" :now />
				</template>
			</Column>

			<Column header="Notes">
				<template #body="{ data: entry }: { data: TimeEntry }">
					<div class="flex flex-wrap gap-1">
						<Chip
							v-if="entry.notes"
							class="py-1"
							v-tooltip.bottom="entry.notes"
						>
							<FontAwesomeIcon
								:icon="faNoteSticky"
								aria-hidden="true"
							/>
							<span class="sr-only xl:not-sr-only">Notes</span>
						</Chip>

						<Chip
							v-if="entry.auto"
							class="py-1 bg-yellow-500 text-gray-950"
							v-tooltip.bottom="
								'This entry was automatically closed at the end of the day.'
							"
						>
							<FontAwesomeIcon
								:icon="faHourglassEnd"
								aria-hidden="true"
							/>
							<span class="sr-only xl:not-sr-only">Auto</span>
						</Chip>
					</div>
				</template>
			</Column>

			<Column
				header="Actions"
				class="text-end"
				:pt="{ columnHeaderContent: { class: 'justify-end' } }"
			>
				<template #body="{ data: entry }: { data: TimeEntry }">
					<TimeEntryActionButtons
						:entry
						@checkout="updateEntry"
						@delete="deleteEntry(entry.id)"
					/>
				</template>
			</Column>

			<template #empty>
				<p>This volunteer doesn't have any time entries.</p>
			</template>
		</DataTable>
	</Panel>
</template>

<script setup lang="ts">
import { useTime } from '../lib/time';
import type Volunteer from '../data/Volunteer';
import type TimeEntry from '../data/TimeEntry';
import type { TimeEntryId } from '../data/TimeEntry';

import Duration from './Duration.vue';
import TimeEntryActionButtons from './TimeEntryActionButtons.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faHourglassEnd, faNoteSticky } from '@fortawesome/free-solid-svg-icons';

defineProps<{ now?: number }>();
const volunteer = defineModel<Volunteer>();

const { isoToDateTimeString } = useTime();

/**
 * Updates a time entry in the entries array with changes from a new version of it
 */
function updateEntry(newEntry: TimeEntry) {
	const entry = volunteer.value!.stats.entries.find((entry) => entry.id === newEntry.id);
	Object.assign(entry!, newEntry);
}

/**
 * Removes a time entry from the entries array
 */
function deleteEntry(id: TimeEntryId) {
	const entryIdx = volunteer.value!.stats.entries.findIndex((entry) => entry.id === id);
	volunteer.value!.stats.entries.splice(entryIdx, 1);
}
</script>
