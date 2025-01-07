<template>
	<span
		v-if="short"
		v-tooltip.bottom="
			tzDate.toLocaleString(DateTime.DATETIME_MED_WITH_WEEKDAY)
		"
	>
		{{ tzDate.toLocaleString(DateTime.DATETIME_SHORT) }}
	</span>
	<span v-else>
		{{ tzDate.toLocaleString(DateTime.DATETIME_MED_WITH_WEEKDAY) }}
	</span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { DateTime } from 'luxon';
import { useTime } from '@/lib/time';

const { dateToPreferredTime, isoToPreferredTime } = useTime();

const { date, short = true } = defineProps<{
	date: Date | string;
	short?: boolean;
}>();

const tzDate = computed(() => {
	if (typeof date === 'string') return isoToPreferredTime(date);
	return dateToPreferredTime(date);
});
</script>
