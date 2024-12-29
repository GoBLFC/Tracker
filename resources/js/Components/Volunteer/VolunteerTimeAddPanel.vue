<template>
	<Panel header="Add Shift">
		<div class="flex flex-wrap gap-4">
			<FloatLabel variant="on" class="grow w-[13em]">
				<DatePicker
					v-model="start"
					show-time
					date-format="yy-mm-dd"
					hour-format="12"
					show-icon
					icon-display="input"
					fluid
					:input-id="startId"
				/>

				<label :for="startId">In time</label>
			</FloatLabel>

			<FloatLabel variant="on" class="grow w-[13em]">
				<DatePicker
					v-model="stop"
					show-time
					date-format="yy-mm-dd"
					hour-format="12"
					show-icon
					icon-display="input"
					fluid
					:input-id="stopId"
				/>

				<label :for="startId">Out time</label>
			</FloatLabel>

			<DepartmentSelector v-model="department" :departments />
		</div>

		<InputGroup class="w-full mt-4">
			<FloatLabel variant="on">
				<InputText v-model="notes" :id="notesId" />
				<label :for="notesId">Notes</label>
			</FloatLabel>

			<ResponsiveButton
				severity="success"
				class="shrink-0"
				:label="!start || stop ? 'Add Shift' : 'Check In'"
				:icon="!start || stop ? faCalendarPlus : faArrowRightToBracket"
				:loading="request.processing.value"
				:disabled="!start || !department || request.processing.value"
				@click="store"
			/>
		</InputGroup>
	</Panel>
</template>

<script setup lang="ts">
import { ref, useId } from 'vue';
import { useTime } from '@/lib/time';
import { useToast } from '@/lib/toast';
import { useRequest } from '@/lib/request';
import type Volunteer from '@/data/Volunteer';
import type Event from '@/data/Event';
import type Department from '@/data/Department';
import type TimeEntry from '@/data/TimeEntry';

import { faArrowRightToBracket, faCalendarPlus } from '@fortawesome/free-solid-svg-icons';
import DepartmentSelector from './DepartmentSelector.vue';
import ResponsiveButton from '../Common/ResponsiveButton.vue';

const { event, departments } = defineProps<{
	event: Event;
	departments: Department[];
}>();
const volunteer = defineModel<Volunteer>();

const { dateToTrackerTime } = useTime();
const toast = useToast();
const request = useRequest();

const start = ref<Date | null>(null);
const stop = ref<Date | null>(null);
const department = ref<Department | null>(null);
const notes = ref('');

const startId = useId();
const stopId = useId();
const notesId = useId();

/**
 * Sends a request to store a new time entry and updates the volunteer appropriately
 */
async function store() {
	const dept = department.value!;
	const isOngoing = !stop.value;

	// Send the request
	const { time_entry: newEntry } = await request.put<{
		time_entry: TimeEntry;
	}>(['tracker.time.store', volunteer.value!.user.id], {
		event_id: event.id,
		department_id: dept.id,
		start: dateToTrackerTime(start.value!).toISO(),
		stop: !isOngoing ? dateToTrackerTime(stop.value!).toISO() : undefined,
		notes: notes.value,
	});

	// Store the new time entry
	newEntry.department = dept;
	volunteer.value!.time.entries.push(newEntry);

	toast.success(isOngoing ? 'User checked in.' : 'Added time entry.');
}
</script>
