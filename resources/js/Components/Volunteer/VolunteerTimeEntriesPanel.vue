<template>
	<Panel header="Shift Log">
		<DataTable
			:value="values"
			data-key="id"
			paginator
			:rows="10"
			:rows-per-page-options="[5, 10, 15, 20]"
			:always-show-paginator="false"
			sortable
			sort-field="start"
			:sort-order="1"
			scrollable
			scroll-height="flex"
			:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
			class="w-full"
		>
			<Column field="start" header="In" sortable data-type="date">
				<template #body="{ data: entry }: { data: TimeEntry }">
					<DateTime :date="entry.start" />
				</template>
			</Column>

			<Column field="stop" header="Out" sortable data-type="date">
				<template #body="{ data: entry }: { data: TimeEntry }">
					<DateTime v-if="entry.stop" :date="entry.stop" />
				</template>
			</Column>

			<Column field="department.name" header="Department" sortable />

			<Column
				field="duration"
				header="Worked"
				sortable
				data-type="number"
			>
				<template #body="{ data: entry }: { data: TimeEntry }">
					<Duration :start="entry.start" :stop="entry.stop" :now />
				</template>
			</Column>

			<Column field="earned" header="Earned" sortable data-type="number">
				<template #body="{ data: entry }: { data: TimeEntry }">
					<Duration :ms="entry.earned * 1000" :now />
				</template>
			</Column>

			<Column header="Notes">
				<template #body="{ data: entry }: { data: TimeEntry }">
					<div class="flex flex-wrap gap-2">
						<ResponsiveTag
							v-if="entry.notes"
							label="Notes"
							:tooltip="entry.notes"
							:icon="faNoteSticky"
							breakpoint="xl"
						/>

						<ResponsiveTag
							v-if="entry.auto"
							label="Auto"
							tooltip="This entry was automatically closed at the end of the day."
							:icon="faHourglassEnd"
							breakpoint="xl"
							severity="warn"
						/>
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
import { computed } from 'vue';
import TimeEntry from '@/data/impl/TimeEntry';
import type Volunteer from '@/data/Volunteer';
import type RawTimeEntry from '@/data/TimeEntry';
import type { TimeEntryId } from '@/data/TimeEntry';

import { faHourglassEnd, faNoteSticky } from '@fortawesome/free-solid-svg-icons';
import TimeEntryActionButtons from '../Manage/TimeEntryActionButtons.vue';
import ResponsiveTag from '../Common/ResponsiveTag.vue';
import DateTime from '../Common/DateTime.vue';
import Duration from '../Common/Duration.vue';

defineProps<{ now?: number }>();
const volunteer = defineModel<Volunteer>();

const values = computed(() => (volunteer.value?.time?.entries ? TimeEntry.load(volunteer.value.time.entries) : []));

/**
 * Updates a time entry in the entries array with changes from a new version of it
 */
function updateEntry(newEntry: RawTimeEntry) {
	const entry = volunteer.value!.time.entries.find((entry) => entry.id === newEntry.id);
	Object.assign(entry!, newEntry);
}

/**
 * Removes a time entry from the entries array
 */
function deleteEntry(id: TimeEntryId) {
	const entryIdx = volunteer.value!.time.entries.findIndex((entry) => entry.id === id);
	volunteer.value!.time.entries.splice(entryIdx, 1);
}
</script>
