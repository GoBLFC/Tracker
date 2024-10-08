<template>
	<div class="card">
		<div class="card-header">Time Log</div>

		<div v-if="volunteer.stats.entries.length > 0" class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-dark table-striped mb-0">
					<thead>
						<tr>
							<th scope="col">In</th>
							<th scope="col">Out</th>
							<th scope="col">Department</th>
							<th scope="col">Worked</th>
							<th scope="col">Earned</th>
							<th scope="col">Notes</th>
							<th scope="col" class="text-end">Actions</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="entry of volunteer.stats.entries">
							<td>{{ isoToDateTimeString(entry.start) }}</td>
							<td>
								{{
									entry.stop
										? isoToDateTimeString(entry.stop)
										: ""
								}}
							</td>
							<td>{{ entry.department.name }}</td>
							<td>
								<Duration
									:start="entry.start"
									:stop="entry.stop"
									:now
								/>
							</td>
							<td>
								<Duration
									:start="entry.start"
									:stop="entry.stop"
									:now
								/>
							</td>
							<td>
								<span
									v-if="entry.notes"
									class="badge rounded-pill text-bg-info info-badge"
									:title="entry.notes"
									data-bs-toggle="tooltip"
								>
									Notes
								</span>

								<span
									v-if="entry.auto"
									class="badge rounded-pill text-bg-warning info-badge"
									title="This entry was automatically closed at the end of the day."
									data-bs-toggle="tooltip"
								>
									Auto
								</span>
							</td>
							<td>
								<TimeEntryActionButtons
									:entry
									@checkout="updateEntry"
									@delete="deleteEntry(entry.id)"
								/>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<p v-else class="card-body mb-0">
			This user doesn't have any time entries.
		</p>
	</div>
</template>

<script setup>
import { useTime } from "../lib/time";
import Duration from "./Duration.vue";
import TimeEntryActionButtons from "./TimeEntryActionButtons.vue";

defineProps({
	now: { type: Number, required: false },
});
const volunteer = defineModel();

const { isoToDateTimeString } = useTime();

/**
 * Updates a time entry in the entries array with changes from a new version of it
 * @param {Object} newEntry
 */
function updateEntry(newEntry) {
	const entry = volunteer.value.stats.entries.find(
		(entry) => entry.id === newEntry.id
	);
	Object.assign(entry, newEntry);
}

/**
 * Removes a time entry from the entries array
 * @param {string} id
 */
function deleteEntry(id) {
	const entryIdx = volunteer.value.stats.entries.findIndex(
		(entry) => entry.id === id
	);
	volunteer.value.stats.entries.splice(entryIdx, 1);
}
</script>
