import { ref, onMounted, onUnmounted } from 'vue';
import { DateTime, Duration } from 'luxon';
import { humanizer } from 'humanize-duration';
import { useSettings } from './settings';

/**
 * Composable for working with times in Tracker's configured timezone
 */
export function useTime() {
	const { timezone } = useSettings();

	/**
	 * Parses an ISO 8601 datetime string and converts it to Tracker's configured timezone
	 * @param {string} iso
	 * @returns {DateTime}
	 */
	function isoToTrackerTime(iso) {
		return DateTime.fromISO(iso).setZone(timezone.value);
	}

	/**
	 * Parses an ISO 8601 datetime string, converts it to Tracker's configured timezone, and builds a human-friendly
	 * datetime string (including abbreviated weekday) using the browser's default locale.
	 * @param {string} iso
	 * @returns {string}
	 */
	function isoToDateTimeString(iso) {
		return isoToTrackerTime(iso).toLocaleString(DateTime.DATETIME_MED_WITH_WEEKDAY);
	}

	return {
		timezone,
		isoToTrackerTime,
		isoToDateTimeString,
	};
}

/**
 * Composable to get a live-updating "now" (current timestamp) value
 * @param {Object} [options]
 * @param {boolean} [options.autoStart=true] Whether to automatically start ticking on mount
 * @param {boolean} [options.autoStop=true] Whether to automatically stop ticking on unmount
 */
export function useNow({ autoStart = true, autoStop = true } = {}) {
	const now = ref(Date.now());
	const isTicking = ref(false);
	let tickInterval = null;

	if (autoStart) onMounted(startTicking);
	if (autoStop) onUnmounted(stopTicking);

	/**
	 * Ticks the clock (sets the `now` value to the current time)
	 */
	function tick() {
		now.value = Date.now();
	}

	/**
	 * Starts the tick interval (ticking every second)
	 */
	function startTicking() {
		if (tickInterval) return;

		tickInterval = setInterval(tick, 1000);
		isTicking.value = true;
	}

	/**
	 * Stops the tick interval
	 */
	function stopTicking() {
		if (!tickInterval) return;

		clearInterval(tickInterval);
		tickInterval = null;
		isTicking.value = false;
	}

	return {
		now,
		isTicking,
		tick,
		startTicking,
		stopTicking,
	};
}

/**
 * Duration humanizer with an additional "shortEn" language defined that uses one/two-letter abbreviations for each unit
 */
export const humanizeDuration = humanizer({
	languages: {
		shortEn: {
			y: () => 'y',
			mo: () => 'mo',
			w: () => 'w',
			d: () => 'd',
			h: () => 'h',
			m: () => 'm',
			s: () => 's',
			ms: () => 'ms',
		},
	},
});

/**
 * Builds a humanized duration string in the form of "3h 25m".
 * Hours are the max unit and seconds are the minimum.
 * A maximum of two units will be used, with the smaller of the two being rounded.
 * @param {number} durationMs
 * @param {boolean} [round=true] Whether to round the final unit
 * @returns {string}
 */
export function shortDuration(durationMs, round = true) {
	return humanizeDuration(durationMs, {
		language: 'shortEn',
		units: ['h', 'm', 's'],
		largest: 2,
		round,
		maxDecimalPoints: 0,
		delimiter: ' ',
		spacer: '',
	});
}

/**
 * Builds a humanized duration string in the form of a clock time like "5:03:25".
 * If there are 0 hours, they will be omitted entirely from the resulting string.
 * @param {number} durationMs
 * @returns {string}
 */
export function clockDuration(durationMs) {
	const duration = Duration.fromMillis(durationMs).shiftTo('hours', 'minutes', 'seconds');
	if (duration.hours > 0) return duration.toFormat('h:mm:ss');
	return duration.toFormat('m:ss');
}

/**
 * Calculates the duration of a time period in milliseconds using two ISO timestamps,
 * or one ISO timestamp and the current time
 * @param {string} start
 * @param {string} [stop]
 * @returns {number}
 */
export function getDuration(start, stop) {
	const startDate = new Date(start);
	const stopDate = stop ? new Date(stop) : new Date();
	return stopDate.getTime() - startDate.getTime();
}