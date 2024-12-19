<template>
	<div
		class="flex flex-wrap px-4 py-6 justify-around gap-8 lg:gap-16 text-center"
	>
		<div v-if="ongoing" class="flex flex-col gap-1">
			<div
				class="flex flex-col lg:flex-row items-center gap-3 text-2xl md:text-4xl"
			>
				<FontAwesomeIcon :icon="faClock" />
				<Duration format="clock" :start="ongoing.start" :now />
			</div>
			<div class="uppercase text-muted-color">
				Shift Duration
				<HelpIcon
					text="Time clocked so far in the current shift. This doesn't include any bonuses that may apply."
					class="ms-1"
				/>
			</div>
		</div>

		<div class="flex flex-col gap-1">
			<div
				class="flex flex-col lg:flex-row items-center gap-3 text-2xl md:text-4xl"
			>
				<FontAwesomeIcon :icon="faClock" />
				<Duration
					:start="dayStart"
					:ms="ongoing ? undefined : time.day * 1000"
					:now="ongoing ? now : undefined"
				/>
			</div>
			<div class="uppercase text-muted-color">
				Time Today
				<HelpIcon
					text="Time clocked for the current day. This doesn't include any bonuses that may apply."
					class="ms-1"
				/>
			</div>
		</div>

		<div class="flex flex-col gap-1">
			<div
				class="flex flex-col lg:flex-row items-center gap-3 text-2xl md:text-4xl"
			>
				<FontAwesomeIcon :icon="faClock" />
				<Duration
					:start="totalStart"
					:ms="ongoing ? undefined : time.total * 1000"
					:now="ongoing ? now : undefined"
				/>
			</div>
			<div class="uppercase text-muted-color">
				Time Earned
				<HelpIcon
					text="Total time earned during the entire event, including any bonuses that may apply"
					class="ms-1"
				/>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type VolunteerTime from '../data/VolunteerTime';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faClock } from '@fortawesome/free-regular-svg-icons';
import HelpIcon from './HelpIcon.vue';
import Duration from './Duration.vue';

const { time } = defineProps<{
	time: VolunteerTime;
	now?: number;
}>();

const ongoing = computed(() => time.entries.find((entry) => !entry.stop));
const dayStart = computed(() => (ongoing ? Date.now() - time.day * 1000 : null));
const totalStart = computed(() => (ongoing ? Date.now() - time.total * 1000 : null));
</script>
