<template>
	<EventDataPage
		title="Manager Dashboard"
		:event
		:events
		:resolver="eventRequestResolver"
	>
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
					:entries="ongoingEntries"
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
					:event
					class="w-full"
					@select="loadVolunteer"
				/>
			</FullContentHeightPanel>

			<!-- Volunteer details -->
			<VolunteerManagePanel
				v-if="volunteer"
				ref="volunteer-panel"
				class="flex-auto min-w-[30%]"
				:model-value="volunteer"
				:event="event!"
				:rewards
				:departments
				:now
				@close="resetVolunteer"
			/>
		</div>

		<div class="flex flex-col xl:flex-row xl:flex-wrap gap-4">
			<!-- Create volunteer -->
			<VolunteerCreatePanel class="flex-1 min-w-[30%]" />

			<!-- Quick settings -->
			<QuickSettingsPanel class="flex-1 min-w-[30%]" />
		</div>
	</EventDataPage>
</template>

<script setup lang="ts">
import { useTemplateRef, nextTick } from 'vue';
import { router, usePoll } from '@inertiajs/vue3';
import { useNow } from '@/lib/time';
import { useRoute } from '@/lib/route';
import type Volunteer from '@/data/Volunteer';
import type Event from '@/data/Event';
import type { EventId } from '@/data/Event';
import type Department from '@/data/Department';
import type Reward from '@/data/Reward';
import type TimeEntryActivity from '@/data/TimeEntryActivity';
import type TimeEntry from '@/data/TimeEntry';
import type { UserId } from '@/data/User';

import QuickSettingsPanel from '@/Components/Manage/QuickSettingsPanel.vue';
import TimeActivitiesTable from '@/Components/Activity/TimeActivitiesTable.vue';
import TimeEntriesTable from '@/Components/TimeEntry/TimeEntriesTable.vue';
import VolunteerSearchTable from '@/Components/Volunteer/VolunteerSearchTable.vue';
import VolunteerManagePanel from '@/Components/Volunteer/VolunteerManagePanel.vue';
import VolunteerCreatePanel from '@/Components/Volunteer/VolunteerCreatePanel.vue';
import FullContentHeightPanel from '@/Components/Common/FullContentHeightPanel.vue';
import EventDataPage from '@/Components/App/EventDataPage.vue';

const { event, volunteer } = defineProps<{
	event: Event | null;
	events: Event[];
	departments: Department[];
	rewards: Reward[];
	recentTimeActivities?: TimeEntryActivity[];
	ongoingEntries?: TimeEntry[];
	volunteer?: Volunteer | null;
}>();

const route = useRoute();
const { now } = useNow();
usePoll(15000, {
	only: ['recentTimeActivities', 'ongoingEntries', 'volunteer'],
});

/**
 * Resolves an event ID to a navigable URL and additional properties to pass to the router
 */
function eventRequestResolver(eventId: EventId) {
	return {
		url: route('management.manage', eventId),
		only: ['ongoingEntries', 'recentTimeActivities', 'volunteer', 'rewards'],
	};
}

//
// Volunteer loading
//
const volunteerPanel = useTemplateRef('volunteer-panel');

/**
 * Loads all data for a volunteer user to provide to the user management card
 */
async function loadVolunteer(userId: UserId, attention = true) {
	router.get(
		route('management.manage.volunteer', {
			event: event!.id,
			user: userId,
		}),
		undefined,
		{
			preserveScroll: true,
			preserveState: true,
			only: ['volunteer'],
			onSuccess() {
				if (!attention) return;
				nextTick(() => {
					volunteerPanel.value!.attention();
				});
			},
		},
	);
}

/**
 * Clears the volunteer information, thus closing the volunteer card
 */
function resetVolunteer() {
	router.push({
		url: route('management.manage', { event: event!.id }),
		props: (current) => ({ ...current, volunteer: null }),
		preserveScroll: true,
		preserveState: true,
	});
}
</script>
