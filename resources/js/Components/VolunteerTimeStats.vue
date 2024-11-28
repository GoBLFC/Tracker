<template>
	<div
		class="flex flex-wrap px-4 py-6 justify-around gap-8 lg:gap-16 text-center"
	>
		<div v-if="ongoing">
			<div
				class="flex flex-col lg:flex-row items-center gap-3 text-2xl md:text-4xl"
			>
				<FontAwesomeIcon :icon="faClock" />
				<Duration format="clock" :start="ongoing.start" :now />
			</div>
			<div class="uppercase text-muted-color">Shift Duration</div>
		</div>

		<div>
			<div
				class="flex flex-col lg:flex-row items-center gap-3 text-2xl md:text-4xl"
			>
				<FontAwesomeIcon :icon="faClock" />
				<Duration
					:start="dayStart"
					:ms="ongoing ? undefined : stats.day * 1000"
					:now="ongoing ? now : undefined"
				/>
			</div>
			<div class="uppercase text-muted-color">Time Today</div>
		</div>

		<div>
			<div
				class="flex flex-col lg:flex-row items-center gap-3 text-2xl md:text-4xl"
			>
				<FontAwesomeIcon :icon="faClock" />
				<Duration
					:start="totalStart"
					:ms="ongoing ? undefined : stats.total * 1000"
					:now="ongoing ? now : undefined"
				/>
			</div>
			<div class="uppercase text-muted-color">Time Earned</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type TimeStats from '../data/TimeStats';
import type TimeEntry from '../data/TimeEntry';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faClock } from '@fortawesome/free-regular-svg-icons';
import Duration from './Duration.vue';

const { ongoing, stats } = defineProps<{
	stats: TimeStats;
	ongoing?: TimeEntry;
	now?: number;
}>();

const dayStart = computed(() => (ongoing ? Date.now() - stats.day * 1000 : null));
const totalStart = computed(() => (ongoing ? Date.now() - stats.total * 1000 : null));
</script>
