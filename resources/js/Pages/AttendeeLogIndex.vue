<template>
	<EventDataPage
		title="Attendee Logs"
		:event
		:events
		:resolver="eventRequestResolver"
	>
		<FullContentHeightPanel header="Attendee Logs" class="grow">
			<AttendeeLogsTable
				:attendee-logs="attendeeLogs"
				:rows="isAdmin ? 10 : 15"
				@select="viewAttendeeLog"
			/>
		</FullContentHeightPanel>

		<AttendeeLogCreatePanel v-if="isAdmin" :event="event!" />
	</EventDataPage>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useUser } from '@/lib/user';
import type AttendeeLog from '@/data/AttendeeLog';
import type { AttendeeLogId } from '@/data/AttendeeLog';
import type Event from '@/data/Event';
import type { EventId } from '@/data/Event';

import AttendeeLogsTable from '@/Components/AttendeeLog/AttendeeLogsTable.vue';
import AttendeeLogCreatePanel from '@/Components/AttendeeLog/AttendeeLogCreatePanel.vue';
import FullContentHeightPanel from '@/Components/Common/FullContentHeightPanel.vue';
import EventDataPage from '@/Components/App/EventDataPage.vue';

const { event } = defineProps<{
	event: Event | null;
	events?: Event[] | null;
	attendeeLogs: AttendeeLog[];
}>();

const { isAdmin } = useUser();

/**
 * Resolves an event ID to a navigable URL and additional properties to pass to the router
 */
function eventRequestResolver(eventId: EventId) {
	return {
		url: route('events.attendee-logs.index', eventId),
		only: ['event', 'attendeeLog', 'attendeeLogs'],
	};
}

/**
 * Loads all data for a volunteer user to provide to the user management card
 */
async function viewAttendeeLog(logId: AttendeeLogId) {
	router.get(route('attendee-logs.show', logId));
}
</script>
