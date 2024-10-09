<template>
	{{ text }}
</template>

<script setup>
import { computed, toRef } from 'vue';
import { shortDuration, clockDuration } from '../lib/time';

const { start, stop, ms, format, now } = defineProps({
	start: { type: [Number, String, null], required: false },
	stop: { type: [Number, String, null], required: false },
	ms: { type: Number, required: false },
	format: { type: String, default: 'short' },
	now: { type: Number, required: false },
});

const startMs = computed(() => (start ? parseTime(start) : null));
const stopMs = computed(() => (stop ? parseTime(stop) : now));
const durationMs = toRef(() => ms ?? stopMs.value - startMs.value);
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
 * Parses an input value into a millisecond timestamp.
 * @param {number|string} time
 * @returns {number}
 */
function parseTime(time) {
	const type = typeof time;
	if (type === 'number') return time;
	if (type === 'string') return new Date(time).getTime();

	throw new TypeError(`Time must be a number of milliseconds or an ISO 8601 datetime string, received ${time}.`);
}
</script>
