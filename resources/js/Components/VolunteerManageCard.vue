<template>
	<div class="card border-info-subtle">
		<h5 class="card-header text-center">
			{{ volunteer.user.badge_name ?? volunteer.user.username }}
			(#{{ volunteer.user.badge_id }})
		</h5>

		<div class="card-body">
			<VolunteerTimeStats :stats="volunteer.stats" :ongoing :now />

			<VolunteerClaimsCard v-model="volunteer" class="mb-3" :rewards />

			<VolunteerTimeEntriesCard class="mb-3" v-model="volunteer" :now />

			<!-- TODO: Reimplement & extract into own component -->
			<div class="card">
				<div class="card-header">Add Time</div>
				<div class="card-body">
					<div class="row gx-3">
						<div class="col-md-4 col-lg-3 mb-2 mb-md-0">
							<div
								class="input-group"
								id="timeStart"
								data-td-target-input="nearest"
								data-td-target-toggle="nearest"
							>
								<input
									id="timeStartInput"
									type="text"
									class="form-control"
									data-td-target="#timeStart"
									placeholder="Start"
								/>
								<span
									class="input-group-text"
									data-td-target="#timeStart"
									data-td-toggle="datetimepicker"
								>
									<i class="fa-solid fa-calendar"></i>
								</span>
							</div>
						</div>

						<div class="col-md-4 col-lg-3 mb-2 mb-md-0">
							<div
								class="input-group"
								id="timeStop"
								data-td-target-input="nearest"
								data-td-target-toggle="nearest"
							>
								<input
									id="timeStopInput"
									type="text"
									class="form-control"
									data-td-target="#timeStop"
									placeholder="Stop"
								/>
								<span
									class="input-group-text"
									data-td-target="#timeStop"
									data-td-toggle="datetimepicker"
								>
									<i class="fa-solid fa-calendar"></i>
								</span>
							</div>
						</div>

						<div class="col-md-4 col-lg-2 mb-2 mb-md-0">
							<select
								class="form-select w-100"
								title="Department"
								id="dept"
							>
								<option value="" disabled selected hidden>
									Select Department
								</option>
								<option
									v-for="dept of departments"
									:value="dept.id"
								>
									{{ dept.name }}
									{{ dept.hidden ? "(hidden)" : "" }}
								</option>
							</select>
						</div>

						<div class="col-md-12 col-lg-4 mt-md-3 mt-lg-0">
							<div class="input-group">
								<input
									type="text"
									class="form-control"
									placeholder="Notes"
									aria-label="Notes"
									id="notes"
								/>
								<button
									id="checkin"
									class="btn btn-success"
									type="button"
									disabled
								>
									Check In
								</button>
								<button
									id="addtime"
									class="btn btn-success"
									type="button"
									disabled
								>
									Add Time
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed } from "vue";

import VolunteerTimeStats from "./VolunteerTimeStats.vue";
import VolunteerClaimsCard from "./VolunteerClaimsCard.vue";
import VolunteerTimeEntriesCard from "./VolunteerTimeEntriesCard.vue";

defineProps({
	rewards: { type: Array, required: true },
	departments: { type: Array, required: true },
	now: { type: Number, required: false },
});
const volunteer = defineModel();

const ongoing = computed(() =>
	volunteer.value.stats.entries.find((entry) => !entry.stop)
);
</script>
