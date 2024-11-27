<template>
	<div
		class="flex px-4 py-6 justify-around gap-8 sm:gap-16 lg:gap-24 text-center"
	>
		<div v-if="ongoing">
			<div class="mb-2 text-2xl sm:text-4xl lg:text-5xl">
				<FontAwesomeIcon
					:icon="faClock"
					class="me-3"
					aria-hidden="true"
				/>
				<Duration format="clock" :start="ongoing.start" :now />
			</div>
			<div class="uppercase text-muted-color">Shift Duration</div>
		</div>

		<div>
			<div class="mb-2 text-2xl sm:text-4xl lg:text-5xl">
				<FontAwesomeIcon
					:icon="faClock"
					class="me-3"
					aria-hidden="true"
				/>
				<Duration
					:start="dayStart"
					:ms="ongoing ? undefined : stats.day * 1000"
					:now="ongoing ? now : undefined"
				/>
			</div>
			<div class="uppercase text-muted-color">Time Today</div>
		</div>

		<div>
			<div class="mb-2 text-2xl sm:text-4xl lg:text-5xl">
				<FontAwesomeIcon
					:icon="faClock"
					class="me-3"
					aria-hidden="true"
				/>
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
