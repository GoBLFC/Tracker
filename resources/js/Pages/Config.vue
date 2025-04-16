<template>
	<div
		class="container grow mx-auto max-w-[80rem] flex flex-col justify-center gap-4"
	>
		<h1 class="text-3xl font-light">Configuration</h1>

		<Panel header="Active Event">
			<p class="mb-4">
				This is the event that all volunteers and managers will be
				entering/managing time for. When there is no active event,
				volunteers won't be able to check in or out, and managers won't
				be able to view or edit any time entries.
			</p>

			<form
				@submit.prevent="
					activeEventForm.patch(
						route('settings.update', 'active-event')
					)
				"
			>
				<InputGroup>
					<InputGroupAddon
						@click="focusActiveEvent()"
						:id="activeEventLabelId"
					>
						<FontAwesomeIcon :icon="faCalendarDay" />
						<span class="tw-sr-only">Event</span>
					</InputGroupAddon>

					<Select
						ref="activeEventSelect"
						v-model="activeEventForm.value"
						:options="events"
						option-label="name"
						option-value="id"
						placeholder="None"
						filter
						:show-clear="true"
						:disabled="activeEventForm.processing"
						:aria-labelledby="activeEventLabelId"
					/>

					<ResponsiveButton
						:icon="faSave"
						label="Save"
						severity="primary"
						type="submit"
						:loading="activeEventForm.processing"
					/>
				</InputGroup>
			</form>
		</Panel>

		<Panel header="Site Settings">
			<dl
				class="grid grid-cols-[min-content_auto] items-center gap-x-4 gap-y-6"
			>
				<dt>
					<SettingToggleSwitch
						setting="dev-mode"
						:value="current['dev-mode'] as boolean"
						:aria-labelledby="devModeLabelId"
					/>
				</dt>
				<dd>
					<p class="text-lg font-semibold" :id="devModeLabelId">
						Development mode
					</p>
					<p>
						Development mode makes it easier to develop and test
						Tracker by displaying some basic status information on
						every page, relaxing the kiosk authorization
						requirement, greatly extending the auto-logout timer,
						and disabling automatic logging out of ConCat alongside
						Tracker.
						<strong>
							If Tracker is running in a production environment,
							this should be disabled.
						</strong>
					</p>
				</dd>

				<dt>
					<SettingToggleSwitch
						setting="lockdown"
						:value="current['lockdown'] as boolean"
						:aria-labelledby="lockdownLabelId"
					/>
				</dt>
				<dd>
					<p class="text-lg font-semibold" :id="lockdownLabelId">
						Lockdown
					</p>
					<p>
						Locking the site down makes it entirely inaccessible to
						volunteers, prohibiting them from checking in/out or
						even getting their Telegram QR code. Managers and
						administrators can still log in and perform staff
						functions, including checking users in/out on their
						behalf. To simply prevent further time entry for an
						event, it's recommended to instead clear the active
						event above and leave this disabled.
					</p>
				</dd>
			</dl>
		</Panel>
	</div>
</template>

<script setup lang="ts">
import { computed, useId, useTemplateRef } from 'vue';
import { useForm } from '@inertiajs/vue3';

import { useRoute } from '@/lib/route';
import type Setting from '@/data/Setting';
import type Event from '@/data/Event';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCalendarDay, faSave } from '@fortawesome/free-solid-svg-icons';
import ResponsiveButton from '@/Components/Common/ResponsiveButton.vue';
import SettingToggleSwitch from '@/Components/Setting/SettingToggleSwitch.vue';

const { settings } = defineProps<{
	settings: Setting[];
	events: Event[];
}>();

const route = useRoute();
const current = computed(() => {
	const vals: Record<string, string | boolean> = {};
	for (const setting of settings) {
		try {
			vals[setting.name] = JSON.parse(setting.value);
		} catch (err) {
			vals[setting.name] = setting.value;
		}
	}
	return vals;
});

// Basic settings
const devModeLabelId = useId();
const lockdownLabelId = useId();

// Active event
const activeEventForm = useForm({ value: current.value['active-event'] });
const activeEventSelect = useTemplateRef('activeEventSelect');
const activeEventLabelId = useId();

/**
 * Sets focus on the active event dropdown
 */
function focusActiveEvent() {
	// @ts-expect-error
	activeEventSelect.value?.$el?.querySelector('[tabindex]')?.focus();
}
</script>
