<template>
	<div class="table-responsive">
		<table class="table table-dark table-striped w-100 mb-0">
			<thead>
				<tr>
					<th scope="col">ID</th>
					<th scope="col">Username</th>
					<th scope="col">Badge Name</th>
					<th scope="col">Real Name</th>
					<th scope="col">Department</th>
					<th scope="col">Start Time</th>
					<th scope="col">Duration</th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="entry of entries" :key="entry.id">
					<th scope="row">{{ entry.user.badge_id }}</th>
					<td>{{ entry.user.username }}</td>
					<td>{{ entry.user.badge_name }}</td>
					<td>{{ entry.user.full_name }}</td>
					<td>{{ entry.department.name }}</td>
					<td>{{ isoToDateTimeString(entry.start) }}</td>
					<td>
						<Duration
							:start="entry.start"
							:stop="entry.stop"
							:now
						/>
					</td>
					<td>
						<button
							class="btn btn-link btn-sm link-info float-end mx-1 p-0"
							title="Lookup user"
							@click="emit('select', entry.user.id)"
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
