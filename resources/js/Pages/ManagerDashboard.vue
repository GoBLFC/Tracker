<template>
	<div class="flex flex-col h-full gap-4">
		<Head title="Manager Dashboard" />

		<!-- Event selector -->
		<div
			class="flex items-center gap-3"
			:class="{
				'justify-center': events.length === 0,
				'justify-between': events.length > 0,
			}"
		>
			<EventSelector
				:event
				:events
				:resolver="eventRequestResolver"
				action-word="manage"
			/>

			<div
				v-if="isReadOnly"
				class="text-2xl text-muted-color"
				v-tooltip.left="'Read-only'"
			>
				<FontAwesomeIcon :icon="faEye" />
				<span class="sr-only">Read-only</span>
			</div>
		</div>

		<div v-if="event" class="flex flex-col h-full gap-4">
			<div class="flex flex-col xl:flex-row xl:flex-wrap gap-4">
				<!-- Recent activity -->
				<FullContentHeightPanel
					header="Recent Activity"
					class="flex-auto min-w-[30%]"
				>
					<TimeActivitiesTable
						class="w-full"
						:activities="recentTimeActivities"
						:now
						@select="loadVolunteer"
					>
						<template #empty>
							<p>There is no recent time activity.</p>
						</template>
					</TimeActivitiesTable>
				</FullContentHeightPanel>

				<!-- Ongoing shifts -->
				<FullContentHeightPanel
					header="Ongoing Shifts"
					class="flex-auto min-w-[30%]"
				>
					<TimeEntriesTable
						class="w-full"
						:entries="longestOngoingEntries"
						:now
						@select="loadVolunteer"
					>
						<template #empty>
							<p>There aren't any ongoing shifts.</p>
						</template>
					</TimeEntriesTable>
				</FullContentHeightPanel>
			</div>

			<div class="flex flex-col xl:flex-row xl:flex-wrap gap-4">
				<!-- Volunteer search -->
				<FullContentHeightPanel
					header="Volunteer Search"
					class="grow basis-1/3 min-w-[30%]"
				>
					<VolunteerSearchTable
						class="w-full"
						@select="loadVolunteer"
					/>
				</FullContentHeightPanel>

				<!-- Volunteer details -->
				<VolunteerManagePanel
					v-if="volunteer"
					v-model="volunteer"
					ref="volunteer-panel"
					class="flex-auto min-w-[30%]"
					:event="event!"
					:rewards
					:departments
					:now
					@close="volunteer = null"
				/>
			</div>

			<div class="flex flex-col xl:flex-row xl:flex-wrap gap-4">
				<!-- Create volunteer -->
				<VolunteerCreatePanel class="flex-1 min-w-[30%]" />

				<!-- Quick settings -->
				<QuickSettingsPanel class="flex-1 min-w-[30%]" />
			</div>
		</div>

		<p
			v-else
			class="flex h-full items-center justify-center text-xl text-muted-color"
		>
			Select an event to manage above.
		</p>
	</div>
</template>

<script setup lang="ts">
import { ref, watch, useTemplateRef, nextTick, onMounted, onUnmounted, toRef } from 'vue';
import { router, Head } from '@inertiajs/vue3';
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

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEye } from '@fortawesome/free-solid-svg-icons';
import EventSelector from '../Components/EventSelector.vue';
import FullContentHeightPanel from '../Components/FullContentHeightPanel.vue';
import TimeActivitiesTable from '../Components/TimeActivitiesTable.vue';
import TimeEntriesTable from '../Components/TimeEntriesTable.vue';
import VolunteerSearchTable from '../Components/VolunteerSearchTable.vue';
import VolunteerManagePanel from '../Components/VolunteerManagePanel.vue';
import VolunteerCreatePanel from '../Components/VolunteerCreatePanel.vue';
import QuickSettingsPanel from '../Components/QuickSettingsPanel.vue';

const { event } = defineProps<{
	event: Event | null;
	events: Event[];
	departments: Department[];
	rewards: Reward[];
	recentTimeActivities: TimeEntryActivity[];
	longestOngoingEntries: TimeEntry[];
}>();

const route = useRoute();
const { activeEvent } = useSettings();
const { isAdmin } = useUser();
const { now } = useNow();

const isActiveEventSelected = toRef(() => event && event.id !== activeEvent.value?.id);
const isReadOnly = toRef(() => !isAdmin && isActiveEventSelected.value);

/**
 * Resolves an event ID to a navigable URL and additional properties to pass to the router
 */
function eventRequestResolver(eventId: EventId) {
	return {
		url: route('management.manage', eventId),
		only: ['longestOngoingEntries', 'recentTimeActivities', 'rewards'],
	};
}

//
// Volunteer loading
//
const statsRequest = useRequest();
const claimsRequest = useRequest();
const volunteerPanel = useTemplateRef('volunteer-panel');

const volunteer = ref<Volunteer | null>(null);

watch(() => event, resetVolunteer);

/**
 * Loads all data for a volunteer user to provide to the user management card
 */
async function loadVolunteer(userId: UserId, attention = true) {
	const [newVolunteer, { reward_claims: claims }] = await Promise.all([
		statsRequest.get<Volunteer>(['tracker.user.stats.event', [userId, event!.id]]),
		claimsRequest.get<{ reward_claims: RewardClaim[] }>(['users.claims.event', [userId, event!.id]]),
	]);

	volunteer.value = {
		user: newVolunteer.user,
		stats: newVolunteer.stats,
		claims: claims,
	};

	if (attention) {
		nextTick(() => {
			volunteerPanel.value!.attention();
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
