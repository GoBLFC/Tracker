<template>
	<Dialog modal header="Preferences" class="w-full sm:w-[28rem] mx-2">
		<p class="mb-6">These preferences save to your browser.</p>

		<div class="flex flex-col gap-6 pt-1">
			<InputGroup>
				<InputGroupAddon @click="focus(themeId)">
					<FontAwesomeIcon
						:icon="
							theme === 'system'
								? faComputer
								: theme === 'light'
								? faSun
								: faMoon
						"
					/>
				</InputGroupAddon>
				<FloatLabel variant="on">
					<Select
						v-model="theme"
						:options="themeOptions"
						option-label="label"
						option-value="val"
						:label-id="themeId"
						fluid
					/>
					<label :for="themeId">Theme</label>
				</FloatLabel>
			</InputGroup>

			<InputGroup>
				<InputGroupAddon @click="focus(timezoneId)">
					<FontAwesomeIcon :icon="faClock" />
				</InputGroupAddon>
				<FloatLabel variant="on">
					<Select
						v-model="timezone"
						:options="timezoneOptions"
						option-label="label"
						option-value="val"
						:label-id="timezoneId"
						fluid
					/>
					<label :for="timezoneId">Timezone</label>
				</FloatLabel>
			</InputGroup>
		</div>
	</Dialog>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue';
import { useAppSettings, useLocalSettings } from '../lib/settings';
import { getTimezoneLabel } from '../lib/time';
import { useTheme } from '../lib/media-query';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faComputer, faMoon, faSun, faClock } from '@fortawesome/free-solid-svg-icons';

const { timezone: appTimezone } = useAppSettings();
const { timezone, theme } = useLocalSettings();
const { theme: systemTheme } = useTheme();
const timezoneId = useId();
const themeId = useId();

const themeOptions = computed(() => [
	{ label: `System (${systemTheme.value})`, val: 'system' },
	{ label: 'Light', val: 'light' },
	{ label: 'Dark', val: 'dark' },
]);

const timezoneOptions = computed(() => [
	{ label: `Server (${getTimezoneLabel(appTimezone.value)})`, val: 'app' },
	{
		label: `Local (${getTimezoneLabel(Intl.DateTimeFormat().resolvedOptions().timeZone)})`,
		val: 'local',
	},
]);

/**
 * Sets focus on a field
 */
function focus(id: string) {
	document.getElementById(id)?.focus();
}
</script>
