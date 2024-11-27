<template>
	<Panel header="Reward Claims">
		<div
			v-if="rewards.length > 0"
			class="flex flex-col justify-between gap-6 text-center"
			:class="{
				'md:flex-row': rewards.length < 5,
			}"
		>
			<div
				v-for="reward of rewards"
				:key="reward.id"
				class="flex flex-row gap-2 items-center justify-between"
				:class="{
					'md:flex-col': rewards.length < 5,
				}"
			>
				<div class="text-lg">
					<span class="font-semibold">{{ reward.hours }}hr:</span>
					{{ reward.name }}
				</div>

				<VolunteerClaimToggleButton
					v-model="volunteer"
					:reward="reward"
				/>
			</div>
		</div>

		<p v-else>There are no rewards available.</p>
	</Panel>
</template>

<script setup lang="ts">
import type Volunteer from '../data/Volunteer';
import type Reward from '../data/Reward';

import VolunteerClaimToggleButton from './VolunteerClaimToggleButton.vue';

defineProps<{ rewards: Reward[] }>();
const volunteer = defineModel<Volunteer>();
</script>
