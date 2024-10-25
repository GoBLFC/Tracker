<template>
	<div class="card mb-3">
		<h5 class="card-header">Your Functions</h5>

		<div class="card-body">
			<div class="row gx-3 gy-2">
				<div v-if="isGatekeeper" class="col-md">
					<LegacyLink
						to="attendee-logs.index"
						class="btn btn-secondary d-block"
					>
						Attendee Logs
					</LegacyLink>
				</div>

				<div v-if="isLead" class="col-md">
					<LegacyLink
						to="management.lead"
						class="btn btn-secondary d-block"
					>
						Lead Panel
					</LegacyLink>
				</div>

				<div v-if="isManager" class="col-md">
					<Link
						to="management.manage"
						class="btn btn-secondary d-block"
					>
						Management Panel
					</Link>
				</div>

				<div v-if="isAdmin" class="col-md">
					<div class="dropdown">
						<button
							class="btn btn-secondary w-100 dropdown-toggle"
							type="button"
							data-bs-toggle="dropdown"
							aria-expanded="false"
						>
							Admin Panel
						</button>

						<ul class="dropdown-menu dropdown-menu-end">
							<li>
								<LegacyLink
									to="admin.site"
									class="dropdown-item"
								>
									Site
								</LegacyLink>
							</li>
							<li>
								<LegacyLink
									to="admin.users"
									class="dropdown-item"
								>
									Users
								</LegacyLink>
							</li>
							<li>
								<LegacyLink
									to="admin.departments"
									class="dropdown-item"
								>
									Departments
								</LegacyLink>
							</li>
							<li>
								<LegacyLink
									to="admin.events"
									class="dropdown-item"
								>
									Events
								</LegacyLink>
							</li>
							<li>
								<LegacyLink
									to="admin.bonuses"
									class="dropdown-item"
								>
									Bonuses
								</LegacyLink>
							</li>
							<li>
								<LegacyLink
									to="admin.rewards"
									class="dropdown-item"
								>
									Rewards
								</LegacyLink>
							</li>
							<li>
								<LegacyLink
									to="admin.attendee-logs"
									class="dropdown-item"
								>
									Attendee Logs
								</LegacyLink>
							</li>
							<li>
								<LegacyLink
									to="admin.reports"
									class="dropdown-item"
								>
									Reports
								</LegacyLink>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { useUser } from '../lib/user';
import Link from './Link.vue';
import LegacyLink from './LegacyLink.vue';

const { isGatekeeper, isLead, isManager, isAdmin } = useUser();
</script>
