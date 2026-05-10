<template>
	<EventDataPage title="Event Setup" :event :events :resolver="eventRequestResolver">
		<EventManagementPanel :event="event!" />
		<EventDepartmentsCrudPanel :event :departments :readonly="!isAdmin" />
		<EventRewardsCrudPanel :event :rewards :readonly="!isAdmin" />
		<EventBonusesCrudPanel :event :bonuses :departments :readonly="!isAdmin" />
	</EventDataPage>
</template>

<script setup lang="ts">
import { useRoute } from '@/lib/route';
import { useUser } from '@/lib/user';
import type Event from '@/data/Event';
import type { EventId } from '@/data/Event';
import type Department from '@/data/Department';
import type Reward from '@/data/Reward';
import type TimeBonus from '@/data/TimeBonus';

import EventDataPage from '@/Components/App/EventDataPage.vue';
import EventManagementPanel from '@/Components/Event/EventManagementPanel.vue';
import EventDepartmentsCrudPanel from '@/Components/Event/EventDepartmentsCrudPanel.vue';
import EventRewardsCrudPanel from '@/Components/Event/EventRewardsCrudPanel.vue';
import EventBonusesCrudPanel from '@/Components/Event/EventBonusesCrudPanel.vue';

defineProps<{
	event: Event | null;
	events?: Event[] | null;
	departments: Department[] | null;
	rewards: Reward[] | null;
	bonuses: TimeBonus[] | null;
}>();

const route = useRoute();
const { isAdmin } = useUser();

/**
 * Resolves an event ID to a navigable URL and additional properties to pass to the router
 */
function eventRequestResolver(eventId: EventId) {
	return {
		url: route('events.show', eventId),
		only: ['event', 'departments', 'rewards', 'bonuses'],
	};
}
</script>
