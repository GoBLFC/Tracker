<template>
	<div class="card">
		<div class="card-header">Add Time</div>

		<div class="card-body">
			<div class="row gx-3">
				<div class="col-md-4 col-lg-3 mb-2 mb-md-0">
					<DateTimePicker
						v-model="start"
						placeholder="Start"
						name="start"
					/>
				</div>

				<div class="col-md-4 col-lg-3 mb-2 mb-md-0">
					<DateTimePicker
						v-model="stop"
						placeholder="Stop"
						name="stop"
					/>
				</div>

				<div class="col-md-4 col-lg-2 mb-2 mb-md-0">
					<select
						v-model="department"
						class="form-select w-100"
						title="Department"
					>
						<option :value="null" disabled selected hidden>
							Select Department
						</option>
						<option v-for="dept of departments" :value="dept">
							{{ dept.name }}
							{{ dept.hidden ? "(hidden)" : "" }}
						</option>
					</select>
				</div>

				<div class="col-md-12 col-lg-4 mt-md-3 mt-lg-0">
					<div class="input-group">
						<input
							v-model="notes"
							type="text"
							class="form-control"
							placeholder="Notes"
							aria-label="Notes"
						/>
						<button
							class="btn btn-success"
							type="button"
							:disabled="
								!start ||
								!department ||
								request.processing.value
							"
							@click="store"
						>
							<FontAwesomeIcon
								:icon="
									request.processing.value
										? faCircleNotch
										: !start || stop
										? faCalendarPlus
										: faArrowRightToBracket
								"
								:spin="request.processing.value"
								class="me-1"
							/>
							{{ !start || stop ? "Add Time" : "Check In" }}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch, faArrowRightToBracket, faCalendarPlus } from '@fortawesome/free-solid-svg-icons';
import { useTime } from '../lib/time';
import { useToast } from '../lib/toast';
import { useRequest } from '../lib/request';
import type Volunteer from '../data/Volunteer';
import type Event from '../data/Event';
import type Department from '../data/Department';
import type TimeEntry from '../data/TimeEntry';
import DateTimePicker from './DateTimePicker.vue';

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

/**
 * Sends a request to store a new time entry and updates the volunteer appropriately
 */
async function store() {
	const dept = department.value!;
	const isOngoing = !stop.value;

	const { time_entry: newEntry } = await request.put<{
		time_entry: TimeEntry;
	}>(['tracker.time.store', volunteer.value!.user.id], {
		event_id: event.id,
		department_id: dept.id,
		start: dateToTrackerTime(start.value!).toISO(),
		stop: !isOngoing ? dateToTrackerTime(stop.value!).toISO() : undefined,
		notes: notes.value,
	});

	newEntry.department = dept;
	volunteer.value!.stats.entries.push(newEntry);

	toast.success(isOngoing ? 'User checked in.' : 'Added time entry.');
}
</script>
