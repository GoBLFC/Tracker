<template>
	<Panel header="Entry Requirements">
		<form class="flex flex-col gap-5" @submit.prevent="save" @input="form.clearErrors()">
			<p>
				<template v-if="hasAnyRule">
					Allows anyone who meets <strong>any</strong> rule below. Gatekeepers aren't checked.
				</template>
				<template v-else>Open to everyone. Turn on a rule below to restrict who can be logged.</template>
			</p>

			<!-- Staff rule -->
			<div class="flex gap-3">
				<ToggleSwitch v-model="staffOn" :input-id="staffId" class="shrink-0" @change="form.clearErrors()" />
				<div class="flex flex-col gap-1">
					<label :for="staffId" class="font-semibold">Is Staff or above</label>
					<small class="text-muted-color">Tracker role Staff, Lead, Manager, or Admin.</small>
				</div>
			</div>

			<!-- Volunteer hours rule -->
			<div class="flex gap-3">
				<ToggleSwitch v-model="hoursOn" :input-id="hoursToggleId" class="shrink-0" @change="onHoursToggled" />
				<div class="flex flex-col gap-1 min-w-0">
					<div class="flex flex-wrap items-center gap-2">
						<label :for="hoursToggleId" class="font-semibold">Volunteered at least</label>
						<InputNumber
							v-model="hours"
							:input-id="hoursId"
							:min="1"
							:max="999"
							:max-fraction-digits="2"
							:disabled="!hoursOn"
							:invalid="hoursOn && (!hoursValid || Boolean(form.errors.min_volunteer_hours))"
							aria-label="Minimum volunteer hours"
							size="small"
							input-class="w-20"
						/>
						<span class="font-semibold">hours</span>
					</div>
					<small class="text-muted-color">Earned hours, including bonuses, for this log's event.</small>
					<Message
						v-if="hoursOn && (!hoursValid || form.errors.min_volunteer_hours)"
						size="small"
						severity="error"
						variant="simple"
					>
						{{ form.errors.min_volunteer_hours ?? 'Enter at least 1 hour.' }}
					</Message>
				</div>
			</div>

			<!-- Registration level rule -->
			<div class="flex gap-3">
				<ToggleSwitch
					v-model="levelsOn"
					:input-id="levelsToggleId"
					class="shrink-0"
					@change="form.clearErrors()"
				/>
				<div class="flex flex-col gap-2 grow min-w-0">
					<div class="flex flex-col gap-1">
						<label :for="levelsToggleId" class="font-semibold">Has a registration level</label>
						<small class="text-muted-color">From the attendee's ConCat registration.</small>
					</div>

					<template v-if="levelsOn">
						<InputGroup>
							<MultiSelect
								v-model="levels"
								:options="options"
								option-label="label"
								option-value="value"
								display="chip"
								filter
								:loading="registrationLevels === undefined"
								:placeholder="registrationLevels === undefined ? 'Loading levels…' : 'Pick levels'"
								:invalid="Boolean(levelsError) || levels.length === 0"
								aria-label="Allowed registration levels"
								class="grow min-w-0"
								@change="form.clearErrors()"
							/>

							<IconButton
								:icon="faRotate"
								severity="secondary"
								:loading="refresh.processing.value"
								:disabled="registrationLevels === undefined"
								aria-label="Reload levels from ConCat"
								v-tooltip.bottom="'Reload levels from ConCat'"
								@click="refreshLevels"
							/>
						</InputGroup>

						<Message v-if="registrationLevels === null" size="small" severity="warn" variant="simple">
							Couldn't load registration levels from ConCat. Only the levels already saved are listed.
						</Message>
						<Message
							v-if="levelsError || levels.length === 0"
							size="small"
							severity="error"
							variant="simple"
						>
							{{ levelsError ?? 'Pick at least one level.' }}
						</Message>
					</template>
				</div>
			</div>

			<!-- Who can override denials -->
			<template v-if="hasAnyRule">
				<Divider class="my-0" />

				<div class="flex flex-col gap-2">
					<span :id="overrideLabelId" class="font-semibold">Who can let in denied attendees anyway</span>
					<SelectButton
						v-model="gatekeepersCanOverride"
						:options="overrideOptions"
						option-label="label"
						option-value="value"
						:allow-empty="false"
						:aria-labelledby="overrideLabelId"
						size="small"
					/>
					<small class="text-muted-color">
						Overrides are recorded with who approved them and shown in the attendee list and export.
					</small>
				</div>
			</template>

			<div class="flex justify-end">
				<ResponsiveButton
					label="Save Requirements"
					:icon="faFloppyDisk"
					type="submit"
					severity="success"
					:loading="form.processing"
					:disabled="form.processing || !isDirty || !isValid"
				/>
			</div>
		</form>
	</Panel>
</template>

<script setup lang="ts">
import { computed, ref, useId, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useRoute } from '@/lib/route';
import { useInertiaRequest } from '@/lib/request';
import type AttendeeLog from '@/data/AttendeeLog';
import type RegistrationLevel from '@/data/RegistrationLevel';

import { faFloppyDisk, faRotate } from '@fortawesome/free-solid-svg-icons';
import ResponsiveButton from '../Common/ResponsiveButton.vue';
import IconButton from '../Common/IconButton.vue';

const { attendeeLog, registrationLevels } = defineProps<{
	attendeeLog: AttendeeLog;
	/** Levels from ConCat: undefined while loading, null if they couldn't be retrieved */
	registrationLevels?: RegistrationLevel[] | null;
}>();

/** Hours suggested when the volunteer hours rule is first turned on */
const DEFAULT_MIN_HOURS = 12;

const route = useRoute();
const refresh = useInertiaRequest();
const staffId = useId();
const hoursToggleId = useId();
const hoursId = useId();
const levelsToggleId = useId();
const overrideLabelId = useId();

const overrideOptions = [
	{ label: 'Managers & admins', value: false },
	{ label: 'Gatekeepers too', value: true },
];

const staffOn = ref(false);
const hoursOn = ref(false);
const hours = ref<number | null>(null);
const levelsOn = ref(false);
const levels = ref<string[]>([]);
const gatekeepersCanOverride = ref(false);
resetFromLog();

const form = useForm<{
	allowed_registration_levels: string[] | null;
	allow_staff: boolean;
	min_volunteer_hours: number | null;
	gatekeepers_can_override: boolean;
}>({
	allowed_registration_levels: null,
	allow_staff: false,
	min_volunteer_hours: null,
	gatekeepers_can_override: false,
});

const levelsError = computed(
	() =>
		form.errors.allowed_registration_levels ??
		Object.entries(form.errors).find(([key]) => key.startsWith('allowed_registration_levels.'))?.[1],
);

// Options are keyed by product name. Saved levels that ConCat doesn't list (such as renamed products or product IDs)
// are still shown so they can be reviewed and removed.
const options = computed(() => {
	const known = (registrationLevels ?? []).map((level) => ({
		value: level.name,
		label:
			level.display_name && level.display_name !== level.name
				? `${level.display_name} (${level.name})`
				: level.name,
	}));

	const knownNames = new Set((registrationLevels ?? []).map((level) => level.name.toLowerCase()));
	const knownIds = new Set((registrationLevels ?? []).map((level) => level.id.toLowerCase()));
	const unknown = (attendeeLog.allowed_registration_levels ?? [])
		.filter((level) => !knownNames.has(level.toLowerCase()))
		.map((level) => {
			let label = level;
			if (knownIds.has(level.toLowerCase())) label = `${level} (product ID)`;
			else if (registrationLevels) label = `${level} (not in ConCat)`;
			return { value: level, label };
		});

	return [...known, ...unknown];
});

// The values that would be saved, with rules that are switched off cleared
const payload = computed(() => ({
	allowed_registration_levels: levelsOn.value && levels.value.length ? levels.value : null,
	allow_staff: staffOn.value,
	min_volunteer_hours: hoursOn.value ? hours.value : null,
	gatekeepers_can_override: gatekeepersCanOverride.value,
}));

const hoursValid = computed(() => hours.value !== null && hours.value >= 1);
const hasAnyRule = computed(() => staffOn.value || hoursOn.value || levelsOn.value);
const isValid = computed(() => (!hoursOn.value || hoursValid.value) && (!levelsOn.value || levels.value.length > 0));
const isDirty = computed(() => {
	const saved = [...(attendeeLog.allowed_registration_levels ?? [])].sort().join('\n');
	const current = [...(payload.value.allowed_registration_levels ?? [])].sort().join('\n');
	return (
		current !== saved ||
		payload.value.allow_staff !== attendeeLog.allow_staff ||
		payload.value.min_volunteer_hours !== attendeeLog.min_volunteer_hours ||
		payload.value.gatekeepers_can_override !== attendeeLog.gatekeepers_can_override
	);
});

// Reset the inputs only when the saved requirements actually change. The log is reloaded after every scan, and
// comparing by value keeps unsaved edits from being discarded by those reloads.
watch(
	() =>
		JSON.stringify([
			attendeeLog.allowed_registration_levels,
			attendeeLog.allow_staff,
			attendeeLog.min_volunteer_hours,
			attendeeLog.gatekeepers_can_override,
		]),
	resetFromLog,
);

/**
 * Sets the inputs to the requirements currently saved on the log
 */
function resetFromLog() {
	staffOn.value = attendeeLog.allow_staff;
	hoursOn.value = attendeeLog.min_volunteer_hours !== null;
	hours.value = attendeeLog.min_volunteer_hours;
	levelsOn.value = Boolean(attendeeLog.allowed_registration_levels?.length);
	levels.value = [...(attendeeLog.allowed_registration_levels ?? [])];
	gatekeepersCanOverride.value = attendeeLog.gatekeepers_can_override;
}

/**
 * Suggests a default number of hours when the volunteer hours rule is turned on without one
 */
function onHoursToggled() {
	form.clearErrors();
	if (hoursOn.value && hours.value === null) hours.value = DEFAULT_MIN_HOURS;
}

/**
 * Submits the form, saving the entry requirements for the log
 */
function save() {
	if (!isValid.value) return;
	form.transform(() => payload.value).patch(route('attendee-logs.update', attendeeLog.id), {
		replace: true,
		preserveScroll: true,
		preserveState: true,
		only: ['attendeeLog', 'flash'],
	});
}

/**
 * Clears the cached registration levels and loads them from ConCat again
 */
function refreshLevels() {
	refresh.post('attendee-logs.registration-levels.refresh', undefined, {
		only: ['registrationLevels'],
	});
}
</script>
