<template>
	<EventDataPage :title :event :events :resolver="eventRequestResolver">
		<EventManagementPanel :event="event!" :readonly="!isAdmin" />

		<TabbedPanel
			:tabs="[
				{
					label: 'Departments',
					icon: faUsersLine,
					route: ['events.departments.index', event!.id],
					only: ['departments'],
				},
				{
					label: 'Rewards',
					icon: faGift,
					route: ['events.rewards.index', event!.id],
					only: ['rewards'],
				},
				{
					label: 'Bonuses',
					icon: faArrowUpRightDots,
					route: ['events.bonuses.index', event!.id],
					only: ['bonuses', 'departments'],
				},
			]"
			class="grow"
		>
			<EventDepartmentsCrudTable
				v-if="tab === 'departments'"
				:event
				:departments="departments!"
				:readonly="!isAdmin"
			/>
			<EventRewardsCrudTable v-else-if="tab === 'rewards'" :event :rewards="rewards!" :readonly="!isAdmin" />
			<EventBonusesCrudTable
				v-else-if="tab === 'bonuses'"
				:event
				:bonuses="bonuses!"
				:departments="departments!"
				:readonly="!isAdmin"
			/>
		</TabbedPanel>
	</EventDataPage>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useCurrentRoute, useRoute } from '@/lib/route';
import { useUser } from '@/lib/user';
import type Event from '@/data/Event';
import type { EventId } from '@/data/Event';
import type Department from '@/data/Department';
import type Reward from '@/data/Reward';
import type TimeBonus from '@/data/TimeBonus';

import { faArrowUpRightDots, faGift, faUsersLine } from '@fortawesome/free-solid-svg-icons';
import EventDataPage from '@/Components/App/EventDataPage.vue';
import EventManagementPanel from '@/Components/Event/EventManagementPanel.vue';
import EventDepartmentsCrudTable from '@/Components/Event/EventDepartmentsCrudTable.vue';
import EventRewardsCrudTable from '@/Components/Event/EventRewardsCrudTable.vue';
import EventBonusesCrudTable from '@/Components/Event/EventBonusesCrudTable.vue';
import TabbedPanel from '@/Components/Common/TabbedPanel.vue';

const { event, departments, rewards, bonuses } = defineProps<{
	event: Event | null;
	events?: Event[] | null;
	departments?: Department[] | null;
	rewards?: Reward[] | null;
	bonuses?: TimeBonus[] | null;
}>();

const route = useRoute();
const currentRoute = useCurrentRoute();
const { isAdmin } = useUser();

const tab = computed(() => {
	if (currentRoute.value === 'events.departments.index') return 'departments';
	if (currentRoute.value === 'events.rewards.index') return 'rewards';
	if (currentRoute.value === 'events.bonuses.index') return 'bonuses';
	return null;
});

const title = computed(() => {
	if (tab.value === 'departments') return 'Departments';
	if (tab.value === 'rewards') return 'Rewards';
	if (tab.value === 'bonuses') return 'Bonuses';
	return 'Event Setup';
});

/**
 * Resolves an event ID to a navigable URL and additional properties to pass to the router
 */
function eventRequestResolver(eventId: EventId) {
	return {
		url: route(`events.${tab.value ?? 'departments'}.index`, eventId),
		only: ['event', 'departments', 'rewards', 'bonuses'],
	};
}
</script>
