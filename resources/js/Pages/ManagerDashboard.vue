<template>
	<div>
		<div class="card mb-3">
			<h4 class="card-header text-bg-warning">Management Controls</h4>

			<div class="card-body">
				<EventSelector
					:event
					:events
					:resolver="eventRequestResolver"
					action-word="Manage"
					class="mb-4"
				/>

				<div
					v-if="!event || event.id !== activeEvent?.id"
					class="alert alert-info mb-4"
					role="alert"
				>
					<p
						v-if="event && event.id !== activeEvent?.id"
						class="mb-0"
					>
						You are managing data for an inactive event.
						<span v-if="!isAdmin"
							>Everything will be read-only.</span
						>
					</p>
					<p v-else-if="!event" class="mb-0">
						There isn't currently any event running, and you haven't
						selected one above. All time-related
						information/functionality will be unavailable.
					</p>
				</div>

				<UserSearchCard class="mb-4" @select="loadVolunteer" />

				<VolunteerManageCard
					v-if="volunteer"
					v-model="volunteer"
					class="mb-5"
					:rewards
					:departments
					:now
				/>

				<div class="card mb-4">
					<div class="card-header">Recent Check-in / Check-out</div>
					<div
						v-if="recentTimeActivities.length > 0"
						class="card-body p-0"
					>
						<TimeActivitiesTable
							:activities="recentTimeActivities"
							:now
							@select="loadVolunteer"
						/>
					</div>
					<p v-else class="card-body mb-0">
						There are no recent check-ins/check-outs.
					</p>
				</div>

				<div class="card mb-4">
					<div class="card-header">Longest Ongoing Shifts</div>
					<div
						v-if="longestOngoingEntries.length > 0"
						class="card-body p-0"
					>
						<TimeEntriesTable
							:entries="longestOngoingEntries"
							:now
							@select="loadVolunteer"
						/>
					</div>
					<p v-else class="card-body mb-0">
						There are no ongoing shifts.
					</p>
				</div>

				<UserCreateCard header-tag="div" class="mb-4" />

				<div class="card mb-4">
					<div class="card-header">Kiosk Settings</div>
					<div class="card-body">
						<dl class="mb-0">
							<div class="row">
								<dt
									class="col-xl-4 col-md-4 col-sm-12 mb-2 mb-md-0 align-self-center text-center"
								>
									<KioskToggleButton class="float-md-end" />
								</dt>
								<dd class="col-xl-6 col-md-8 col-sm-12 mb-0">
									<p class="mb-0">
										Authorizing this device as a kiosk will
										allow volunteers to check in or out on
										this device. This is required when
										setting up dedicated devices pre-con for
										checking in or out. Kiosks remain
										authorized for
										{{ kioskLifetimeText }}.
									</p>
								</dd>
							</div>
						</dl>
					</div>
				</div>

				<LegacyLink
					to="tracker.index"
					class="btn btn-primary float-end"
				>
					Back
				</LegacyLink>
			</div>
		</div>

		<ManagementNav class="mt-5" />
	</div>
</template>

<script setup>
import { inject, ref, computed, watch } from "vue";
import humanizeDuration from "humanize-duration";
import { useUser } from "../lib/user";
import { useSettings } from "../lib/settings";
import { useNow } from "../lib/time";
import { useRequest } from "../lib/request";
import { useToast } from "../lib/toast";
import LegacyLink from "../Components/LegacyLink.vue";
import EventSelector from "../Components/EventSelector.vue";
import UserSearchCard from "../Components/UserSearchCard.vue";
import VolunteerManageCard from "../Components/VolunteerManageCard.vue";
import TimeActivitiesTable from "../Components/TimeActivitiesTable.vue";
import TimeEntriesTable from "../Components/TimeEntriesTable.vue";
import UserCreateCard from "../Components/UserCreateCard.vue";
import KioskToggleButton from "../Components/KioskToggleButton.vue";
import ManagementNav from "../Components/ManagementNav.vue";

// TODO: auto-refresh activities and entries
const { event, kioskLifetime } = defineProps({
	event: { type: [Object, null], required: true },
	events: { type: Array, required: true },
	departments: { type: Array, required: true },
	rewards: { type: Array, required: true },
	kioskLifetime: { type: Number, required: true },
	recentTimeActivities: { type: Array, required: true },
	longestOngoingEntries: { type: Array, required: true },
});

const route = inject("route");
const toast = useToast();
const { activeEvent } = useSettings();
const { isAdmin } = useUser();
const { now } = useNow();

const kioskLifetimeText = computed(() =>
	humanizeDuration(kioskLifetime * 1000 * 60)
);

/**
 * Resolves an event ID to a navigable URL and additional properties to pass to the router
 * @param {string} eventId
 * @returns {Object}
 */
function eventRequestResolver(eventId) {
	return {
		url: route("management.manage", eventId),
		only: ["longestOngoingEntries", "recentTimeActivities"],
	};
}

//
// Volunteer loading
//
const statsRequest = useRequest();
const claimsRequest = useRequest();

// TODO: auto-refresh volunteer data while displayed
const volunteer = ref(null);

watch(() => event, resetVolunteer);

/**
 * Loads all data for a volunteer user to provide to the user management card
 * @param userId
 */
async function loadVolunteer(userId) {
	try {
		const [timeData, claimData] = await Promise.all([
			statsRequest.get(["tracker.user.stats.event", [userId, event.id]]),
			claimsRequest.get(["users.claims.event", [userId, event.id]]),
		]);

		volunteer.value = {
			user: timeData.user,
			stats: timeData.stats,
			claims: claimData.reward_claims,
		};

		// TODO: scroll to user card
	} catch (err) {
		toast.error(
			"Unable to load volunteer data",
			"See the browser console for more information."
		);
	}
}

/**
 * Clears the volunteer information, thus closing the volunteer card
 */
function resetVolunteer() {
	volunteer.value = null;
}
</script>
