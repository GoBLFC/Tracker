<template>
	<div>
		<Head title="Management" />

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
						<template v-if="!isAdmin">
							Everything will be read-only.
						</template>
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
					ref="volunteer-card"
					class="mb-5"
					:event
					:rewards
					:departments
					:now
					v-model="volunteer"
					@close="volunteer = null"
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

<script setup lang="ts">
import { ref, computed, watch, useTemplateRef, nextTick, onMounted, onUnmounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import humanizeDuration from 'humanize-duration';
import { useUser } from '../lib/user';
import { useSettings } from '../lib/settings';
import { useNow } from '../lib/time';
import { useRequest } from '../lib/request';
import { useRoute } from '../lib/route';
import type Volunteer from '../data/Volunteer';
import type Event from '../data/Event';
import type { EventId } from '../data/Event';
import type Department from '../data/Department';
import type Reward from '../data/Reward';
import type TimeEntryActivity from '../data/TimeEntryActivity';
import type TimeEntry from '../data/TimeEntry';
import type RewardClaim from '../data/RewardClaim';
import type { UserId } from '../data/User';
import LegacyLink from '../Components/LegacyLink.vue';
import EventSelector from '../Components/EventSelector.vue';
import UserSearchCard from '../Components/UserSearchCard.vue';
import VolunteerManageCard from '../Components/VolunteerManageCard.vue';
import TimeActivitiesTable from '../Components/TimeActivitiesTable.vue';
import TimeEntriesTable from '../Components/TimeEntriesTable.vue';
import UserCreateCard from '../Components/UserCreateCard.vue';
import KioskToggleButton from '../Components/KioskToggleButton.vue';
import ManagementNav from '../Components/ManagementNav.vue';

const { event, kioskLifetime } = defineProps<{
	event: Event;
	events: Event[];
	departments: Department[];
	rewards: Reward[];
	kioskLifetime: number;
	recentTimeActivities: TimeEntryActivity[];
	longestOngoingEntries: TimeEntry[];
}>();

const route = useRoute();
const { activeEvent } = useSettings();
const { isAdmin } = useUser();
const { now } = useNow();

const kioskLifetimeText = computed(() => humanizeDuration(kioskLifetime * 1000 * 60));

/**
 * Resolves an event ID to a navigable URL and additional properties to pass to the router
 */
function eventRequestResolver(eventId: EventId) {
	return {
		url: route('management.manage', eventId),
		only: ['longestOngoingEntries', 'recentTimeActivities'],
	};
}

//
// Volunteer loading
//
const statsRequest = useRequest();
const claimsRequest = useRequest();
const volunteerCard = useTemplateRef('volunteer-card');

const volunteer = ref<Volunteer | null>(null);

watch(() => event, resetVolunteer);

/**
 * Loads all data for a volunteer user to provide to the user management card
 */
async function loadVolunteer(userId: UserId, attention = true) {
	const [newVolunteer, { reward_claims: claims }] = await Promise.all([
		statsRequest.get<Volunteer>(['tracker.user.stats.event', [userId, event.id]]),
		claimsRequest.get<{ reward_claims: RewardClaim[] }>(['users.claims.event', [userId, event.id]]),
	]);

	volunteer.value = {
		user: newVolunteer.user,
		stats: newVolunteer.stats,
		claims: claims,
	};

	if (attention) {
		nextTick(() => {
			volunteerCard.value!.attention();
		});
	}
}

/**
 * Clears the volunteer information, thus closing the volunteer card
 */
function resetVolunteer() {
	volunteer.value = null;
}

//
// Auto-refreshing
//
let refreshInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
	refreshInterval = setInterval(() => {
		router.reload({
			only: ['recentTimeActivities', 'longestOngoingEntries'],
		});

		const shouldRefreshVolunteer =
			volunteer.value && !statsRequest.processing.value && !claimsRequest.processing.value;
		if (shouldRefreshVolunteer) loadVolunteer(volunteer.value!.user.id, false);
	}, 15000);
});

onUnmounted(() => {
	clearInterval(refreshInterval!);
	refreshInterval = null;
});
</script>
