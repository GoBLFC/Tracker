<template>
	<div class="card">
		<div class="card-header">Search Volunteers</div>

		<div class="card-body">
			<input
				type="text"
				class="form-control"
				placeholder="Badge Number, Name, Username..."
				aria-label="Search"
				v-debounce:400ms="searchUsers"
				v-model="query"
			/>

			<div v-if="users" class="card mt-3">
				<div v-if="users.length > 0" class="card-body p-0">
					<div class="table-responsive">
						<table
							class="table table-dark table-striped w-100 mb-0"
						>
							<thead>
								<tr>
									<th scope="col">ID</th>
									<th scope="col">Username</th>
									<th scope="col">Badge Name</th>
									<th scope="col">Real Name</th>
									<th scope="col">Status</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="user of users" :key="user.id">
									<th scope="row">{{ user.badge_id }}</th>
									<td>{{ user.username }}</td>
									<td>{{ user.badge_name }}</td>
									<td>
										{{ user.first_name }}
										{{ user.last_name }}
									</td>
									<td>
										<span
											v-if="getDepartment(user)"
											class="badge rounded-pill text-bg-success"
										>
											<FontAwesomeIcon
												:icon="faArrowRightToBracket"
												class="float-start me-1"
											/>
											Checked In:
											{{ getDepartment(user) }}
										</span>
										<span
											v-else
											class="checkin-badge badge rounded-pill text-bg-warning"
										>
											<FontAwesomeIcon
												:icon="faArrowRightFromBracket"
												class="float-start"
											/>
											Checked Out
										</span>
										<span
											v-if="user.role === -2"
											class="badge rounded-pill text-bg-danger"
										>
											Banned
										</span>
									</td>
									<td>
										<button
											class="btn btn-sm btn-info"
											@click="emit('select', user.id)"
										>
											<FontAwesomeIcon
												:icon="faAddressCard"
												class="me-1"
											/>
											Load
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<p v-else class="card-body mb-0">
					There are no users that match your search.
				</p>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import vueDebounce from 'vue-debounce';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faAddressCard, faArrowRightToBracket, faArrowRightFromBracket } from '@fortawesome/free-solid-svg-icons';
import { useRequest } from '../lib/request';
import type User from '../data/User';
import type { UserId } from '../data/User';

const emit = defineEmits<(e: 'select', userId: UserId) => void>();

const vDebounce = vueDebounce({ lock: true });
const request = useRequest();
const query = ref('');
const users = ref<User[] | null>(null);

/**
 * Sends a request to search for users that match the input query
 */
async function searchUsers() {
	if (!query.value.trim()) {
		users.value = null;
		return;
	}

	const { users: resultUsers } = await request.get<{ users: User[] }>('users.search', {
		q: query.value,
	});
	if (resultUsers) users.value = resultUsers;
}

/**
 * Gets the department name for a user's current time entry, if there is one
 */
function getDepartment(user: User): string | null | undefined {
	return user.time_entries?.[0]?.department?.name;
}
</script>
