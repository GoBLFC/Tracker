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
							@click="checkin"
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

<script setup>
import { ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch, faArrowRightToBracket, faCalendarPlus } from '@fortawesome/free-solid-svg-icons';
import { useTime } from '../lib/time';
import { useToast } from '../lib/toast';
import { useRequest } from '../lib/request';
import DateTimePicker from './DateTimePicker.vue';

const { event, departments } = defineProps({
	event: { type: Object, required: true },
	departments: { type: Object, required: true },
});
const volunteer = defineModel();

const { dateToTrackerTime } = useTime();
const toast = useToast();
const request = useRequest();

const start = ref(null);
const stop = ref(null);
const department = ref(null);
const notes = ref('');

/**
 * Sends a request to start an ongoing time entry and updates the volunteer appropriately
 */
async function checkin() {
	const dept = department.value;
	const data = await request.put(['tracker.time.store', volunteer.value.user.id], {
		event_id: event.id,
		department_id: dept.id,
		start: dateToTrackerTime(start.value).toISO(),
		notes: notes.value,
	});

	data.time_entry.department = dept;
	volunteer.value.stats.entries.push(data.time_entry);

	toast.success('User checked in.');
}

/**
 * Sends a request to add a complete time entry and updates the volunteer appropriately
 */
async function addTime() {
	const dept = department.value;
	const data = await request.put(['tracker.time.store', volunteer.value.user.id], {
		event_id: event.id,
		department_id: dept.id,
		start: dateToTrackerTime(start.value).toISO(),
		stop: dateToTrackerTime(stop.value).toISO(),
		notes: notes.value,
	});

	data.time_entry.department = dept;
	volunteer.value.stats.entries.push(data.time_entry);

	toast.success('Added time entry.');
}
</script>
