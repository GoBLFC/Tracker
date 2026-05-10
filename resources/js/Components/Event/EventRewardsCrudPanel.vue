<template>
	<FullContentHeightPanel header="Rewards">
		<template #icons>
			<HelpDialogButton header="Rewards">
				<p>
					Rewards are milestones for volunteer hours during an event. Volunteers are automatically notified
					when they reach the appropriate number of hours for a reward, and managers can claim rewards for
					each volunteer.
				</p>
			</HelpDialogButton>
		</template>

		<CrudTable
			entity-name="reward"
			route-slug="rewards"
			:create-route="['events.rewards.store', event!.id]"
			:fields="[
				{ key: 'name', label: 'Name', required: true },
				{ key: 'description', label: 'Description', type: 'textarea', required: true },
				{ key: 'hours', label: 'Hours', type: 'number', required: true, min: 1, max: 168, class: 'w-32' },
			]"
			:readonly
			:items="rewards ?? []"
			:table-props="{ sortField: 'hours' }"
			:skeleton="!rewards"
		/>
	</FullContentHeightPanel>
</template>

<script setup lang="ts">
import type TrackerEvent from '@/data/Event';
import type Reward from '@/data/Reward';

import FullContentHeightPanel from '@/Components/Common/FullContentHeightPanel.vue';
import HelpDialogButton from '@/Components/Common/HelpDialogButton.vue';
import CrudTable from '@/Components/App/CrudTable.vue';

const { readonly = false } = defineProps<{
	event: TrackerEvent | null;
	rewards: Reward[] | null;
	readonly?: boolean;
}>();
</script>
