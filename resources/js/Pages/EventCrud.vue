<template>
	<EventDataPage
		title="Event Setup"
		:event
		:events
		:resolver="eventRequestResolver"
	>
		<Panel header="Rewards">
			<CrudTable
				v-if="rewards"
				entity-name="reward"
				route-slug="rewards"
				:create-route="['events.rewards.store', event.id]"
				:fields="[
					{ key: 'name', label: 'Name' },
					{ key: 'description', label: 'Description', type: 'textarea' },
					{ key: 'hours', label: 'Hours', type: 'number', min: 1, max: 168, class: 'w-32' },
				]"
				:readonly="!isAdmin"
				:items="rewards"
				:table-props="{ sortField: 'hours' }"
			/>
		</Panel>

		<Panel header="Bonuses">
			<CrudTable
				v-if="bonuses"
				entity-name="bonus"
				entity-plural="bonuses"
				route-slug="bonuses"
				:create-route="['events.bonuses.store', event.id]"
				:fields="[
					{ key: 'start', label: 'Start', type: 'datetime' },
					{ key: 'stop', label: 'Stop', type: 'datetime' },
					{
						key: 'modifier',
						label: 'Multiplier',
						type: 'number',
						min: 1,
						max: 10,
						step: 0.25,
						fractionDigits: 2,
						default: 1,
						suffix: 'x',
						class: 'w-36'
					},
					{
						key: 'departments',
						label: 'Departments',
						type: 'select',
						multiple: true,
						options: departments.map(dept => ({ label: dept.name, value: dept.id })),
						display: data => (data as string[]).map(id => departments.find(dept => dept.id === id)!.name).join(', '),
					},
				]"
				:readonly="!isAdmin"
				:items="bonuses"
				:table-props="{ sortField: 'start' }"
			/>
		</Panel>
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
import CrudTable from '@/Components/App/CrudTable.vue';

defineProps<{
	event: Event;
	events?: Event[] | null;
	departments: Department[];
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
		only: ['event', 'rewards', 'bonuses'],
	};
}
</script>
