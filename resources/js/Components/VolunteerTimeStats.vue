<template>
	<div class="row">
		<div v-if="ongoing" class="col-md text-center py-4">
			<div class="display-2 fw-normal">
				<i class="fa-regular fa-clock"></i>
				<Duration format="clock" :start="ongoing.start" :now />
			</div>
			<div class="text-uppercase">Shift Duration</div>
		</div>

		<div class="col-md text-center py-4">
			<div class="display-2 fw-normal">
				<i class="fa-regular fa-clock"></i>
				<Duration
					:start="dayStart"
					:ms="ongoing ? undefined : stats.day * 1000"
					:now="ongoing ? now : undefined"
				/>
			</div>
			<div class="text-uppercase">Time Today</div>
		</div>

		<div class="col-md text-center py-4">
			<div class="display-2 fw-normal">
				<i class="fa-regular fa-clock"></i>
				<Duration
					:start="totalStart"
					:ms="ongoing ? undefined : stats.total * 1000"
					:now="ongoing ? now : undefined"
				/>
			</div>
			<div class="text-uppercase">Time Earned</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type TimeStats from '../data/TimeStats';
import type TimeEntry from '../data/TimeEntry';
import Duration from './Duration.vue';

const { ongoing, stats } = defineProps<{
	stats: TimeStats;
	ongoing?: TimeEntry;
	now?: number;
}>();

const dayStart = computed(() => (ongoing ? Date.now() - stats.day * 1000 : null));
const totalStart = computed(() => (ongoing ? Date.now() - stats.total * 1000 : null));
</script>
