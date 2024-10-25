<template>
	{{ text }}
</template>

<script setup lang="ts">
import { computed, toRef } from 'vue';
import { shortDuration, clockDuration } from '../lib/time';

const {
	start,
	stop,
	ms,
	format = 'short',
	now,
} = defineProps<{
	start?: number | string | null;
	stop?: number | string | null;
	ms?: number;
	format?: string;
	now?: number;
}>();

const startMs = computed(() => (start ? parseTime(start) : null));
const stopMs = computed(() => (stop ? parseTime(stop) : now));
const durationMs = toRef(() => ms ?? (stopMs.value ?? 0) - (startMs.value ?? 0));
const text = computed(() => {
	switch (format) {
		case 'short':
			return shortDuration(durationMs.value, false);
		case 'clock':
			return clockDuration(durationMs.value);
		default:
			throw new RangeError(`Duration format property must be one of 'short' or 'clock', received ${format}.`);
	}
});

/**
 * Parses an input value into a millisecond timestamp
 */
function parseTime(time: number | string): number {
	const type = typeof time;
	if (type === 'number') return time as number;
	if (type === 'string') return new Date(time).getTime();

	throw new TypeError(`Time must be a number of milliseconds or an ISO 8601 datetime string, received ${time}.`);
}
</script>
