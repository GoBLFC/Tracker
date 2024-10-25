<template>
	<div class="table-responsive">
		<table class="table table-dark table-striped w-100 mb-0">
			<thead>
				<tr>
					<th scope="col">ID</th>
					<th scope="col">Username</th>
					<th scope="col">Badge Name</th>
					<th scope="col">Real Name</th>
					<th scope="col">Action</th>
					<th scope="col">Time</th>
					<th scope="col">Duration</th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="activity of activities" :key="activity.id">
					<th scope="row">{{ activity.subject.user.badge_id }}</th>
					<td>{{ activity.subject.user.username }}</td>
					<td>{{ activity.subject.user.badge_name }}</td>
					<td>{{ activity.subject.user.full_name }}</td>
					<td>
						<div
							v-if="activity.properties.attributes.stop"
							class="checkin-badge badge rounded-pill text-bg-warning"
						>
							<FontAwesomeIcon
								:icon="faArrowRightFromBracket"
								class="float-start"
							/>
							Checked Out
						</div>
						<div
							v-else
							class="checkin-badge badge rounded-pill text-bg-success"
						>
							<FontAwesomeIcon
								:icon="faArrowRightToBracket"
								class="float-start"
							/>
							Checked In
						</div>
					</td>
					<td>{{ isoToDateTimeString(activity.subject.start) }}</td>
					<td>
						<Duration
							:start="activity.subject.start"
							:stop="activity.subject.stop"
							:now
						/>
					</td>
					<td>
						<button
							class="btn btn-link btn-sm link-info float-end mx-1 p-0"
							title="Lookup user"
							@click="emit('select', activity.subject.user.id)"
						>
							<FontAwesomeIcon :icon="faMagnifyingGlass" />
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faMagnifyingGlass, faArrowRightToBracket, faArrowRightFromBracket } from '@fortawesome/free-solid-svg-icons';
import { useTime } from '../lib/time';
import type TimeEntryActivity from '../data/TimeEntryActivity';
import type { UserId } from '../data/User';
import Duration from './Duration.vue';

defineProps<{
	activities: TimeEntryActivity[];
	now?: number;
}>();
const emit = defineEmits<(e: 'select', userId: UserId) => void>();

const { isoToDateTimeString } = useTime();
</script>
