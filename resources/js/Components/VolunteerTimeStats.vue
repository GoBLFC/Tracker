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
					:ms="ongoing ? null : stats.day * 1000"
					:now="ongoing ? now : null"
				/>
			</div>
			<div class="text-uppercase">Time Today</div>
		</div>

		<div class="col-md text-center py-4">
			<div class="display-2 fw-normal">
				<i class="fa-regular fa-clock"></i>
				<Duration
					:start="totalStart"
					:ms="ongoing ? null : stats.total * 1000"
					:now="ongoing ? now : null"
				/>
			</div>
			<div class="text-uppercase">Time Earned</div>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue';
import Duration from './Duration.vue';

const { ongoing, stats } = defineProps({
	ongoing: { type: [Object, null], required: false },
	stats: { type: Object, required: true },
	now: { type: Number, required: false },
});

const dayStart = computed(() => (ongoing ? Date.now() - stats.day * 1000 : null));
const totalStart = computed(() => (ongoing ? Date.now() - stats.total * 1000 : null));
</script>
