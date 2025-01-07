import { ref, onMounted, onUnmounted } from 'vue';
import { DateTime, Duration } from 'luxon';
import { humanizer } from 'humanize-duration';
import { useAppSettings, useLocalSettings } from './settings';

/**
 * Composable for working with times in Tracker's configured timezone
 */
export function useTime() {
	const { timezone } = useAppSettings();
	const { timezone: preferredTimezone } = useLocalSettings();

	/**
	 * Converts a local JS date to Tracker's configured timezone
	 * @param date
	 * @param keepLocalTime Whether to preserve the local time when converting timezones, thus modifying the timestamp.
	 * Defaults to true if the user's preferred timezone is the app's.
	 */
	function dateToTrackerTime(date: Date, keepLocalTime = preferredTimezone.value === 'app'): DateTime {
		return DateTime.fromJSDate(date).setZone(timezone.value, { keepLocalTime });
	}

	/**
	 * Parses an ISO 8601 datetime string and converts it to Tracker's configured timezone
	 */
	function isoToTrackerTime(iso: string): DateTime {
		return DateTime.fromISO(iso).setZone(timezone.value);
	}

	/**
	 * Converts a local JS date to the user's preferred timezone
	 * @param date
	 * @param keepLocalTime Whether to preserve the local time when converting timezones, thus modifying the timestamp.
	 * Defaults to true if the user's preferred timezone is the app's.
	 */
	function dateToPreferredTime(date: Date, keepLocalTime?: boolean): DateTime {
		if (preferredTimezone.value === 'app') return dateToTrackerTime(date, keepLocalTime);
		return DateTime.fromJSDate(date);
	}

	/**
	 * Parses an ISO 8601 datetime string and converts it to the user's preferred timezone
	 */
	function isoToPreferredTime(iso: string): DateTime {
		if (preferredTimezone.value === 'app') return isoToTrackerTime(iso);
		return DateTime.fromISO(iso);
	}

	/**
	 * Gets the current time in Tracker's configured timezone
	 */
	function now(): DateTime {
		return DateTime.now().setZone(timezone.value);
	}

	return {
		timezone,
		dateToTrackerTime,
		isoToTrackerTime,
		dateToPreferredTime,
		isoToPreferredTime,
		now,
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
	let tickInterval: ReturnType<typeof setInterval> | null = null;

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

		tick();
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
 * @param {boolean} [round=true] Whether to round the final unit
 */
export function shortDuration(durationMs: number, round = true): string {
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
 */
export function clockDuration(durationMs: number): string {
	const duration = Duration.fromMillis(durationMs).shiftTo('hours', 'minutes', 'seconds');
	if (duration.hours > 0) return duration.toFormat('h:mm:ss');
	return duration.toFormat('m:ss');
}

/**
 * Calculates the duration of a time period in milliseconds using two ISO timestamps,
 * or one ISO timestamp and the current time
 */
export function getDuration(start: string, stop?: string): number {
	const startDate = new Date(start);
	const stopDate = stop ? new Date(stop) : new Date();
	return stopDate.getTime() - startDate.getTime();
}

/**
 * Gets the current full label for a given timezone
 * @param timezone IANA timezone identifier (like `America/New_York`)
 * @param short Whether to return just the timezone abbreviation (like `EST`)
 * @returns Full label for the timezone (like `Eastern Standard Time, UTC-5`) or short label (like `EST`)
 */
export function getTimezoneLabel(timezone: string, short = false): string {
	const date = DateTime.now().setZone(timezone);
	return `${date.offsetNameLong}, UTC${date.toFormat('Z')}`;
}
