<template>
	<Panel header="Add Shift">
		<div class="flex flex-wrap gap-2">
			<FloatLabel variant="on">
				<DatePicker
					v-model="start"
					show-time
					date-format="yy-mm-dd"
					hour-format="12"
					show-icon
					icon-display="input"
					class="grow w-[13em]"
					:input-id="startId"
				/>

				<label :for="startId">In time</label>
			</FloatLabel>

			<FloatLabel variant="on">
				<DatePicker
					v-model="stop"
					show-time
					date-format="yy-mm-dd"
					hour-format="12"
					show-icon
					icon-display="input"
					class="grow w-[13em]"
					:input-id="stopId"
				/>

				<label :for="startId">Out time</label>
			</FloatLabel>

			<FloatLabel variant="on">
				<Select
					v-model="department"
					:options="departments"
					class="grow w-64"
					:input-id="deptId"
				>
					<template
						#value="{
							value: dept,
						}: {
							value: Department | null | undefined,
						}"
					>
						<div
							v-if="dept"
							class="flex grow gap-4 justify-between items-center"
						>
							<span class="truncate">{{ dept.name }}</span>
							<Tag
								v-if="dept.hidden"
								value="Hidden"
								severity="secondary"
							/>
						</div>

						<div v-else>&nbsp;</div>
					</template>

					<template
						#option="{ option: dept }: { option: Department }"
					>
						<div
							class="flex grow gap-6 justify-between items-center"
						>
							<span class="truncate">{{ dept.name }}</span>
							<Tag
								v-if="dept.hidden"
								value="Hidden"
								severity="secondary"
							/>
						</div>
					</template>
				</Select>

				<label :for="deptId">Department</label>
			</FloatLabel>

			<InputGroup class="grow w-96">
				<FloatLabel variant="on">
					<InputText v-model="notes" :input-id="notesId" />
					<label :for="notesId">Notes</label>
				</FloatLabel>

				<Button
					severity="success"
					class="shrink-0"
					:label="!start || stop ? 'Add Shift' : 'Check In'"
					:loading="request.processing.value"
					:disabled="
						!start || !department || request.processing.value
					"
					@click="store"
				>
					<template #icon>
						<FontAwesomeIcon
							:icon="
								!start || stop
									? faCalendarPlus
									: faArrowRightToBracket
							"
						/>
					</template>

					<template #loadingicon>
						<FontAwesomeIcon :icon="faCircleNotch" spin />
					</template>
				</Button>
			</InputGroup>
		</div>
	</Panel>
</template>

<script setup lang="ts">
import { ref, useId } from 'vue';
import { useTime } from '../lib/time';
import { useToast } from '../lib/toast';
import { useRequest } from '../lib/request';
import type Volunteer from '../data/Volunteer';
import type Event from '../data/Event';
import type Department from '../data/Department';
import type TimeEntry from '../data/TimeEntry';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch, faArrowRightToBracket, faCalendarPlus } from '@fortawesome/free-solid-svg-icons';

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
const deptId = useId();
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
	volunteer.value!.stats.entries.push(newEntry);

	toast.success(isOngoing ? 'User checked in.' : 'Added time entry.');
}
</script>
