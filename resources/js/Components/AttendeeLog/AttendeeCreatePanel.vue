<template>
	<Panel :header="gatekeeper ? 'Add Gatekeeper' : 'Log Attendee'">
		<div class="flex flex-col gap-6">
			<p v-if="gatekeeper" class="grow">
				Gatekeepers can view, add, and delete attendees in the log, but cannot manage gatekeepers themselves.
				Any volunteer, not just staff, can be added as a gatekeeper.
			</p>

			<p v-else class="grow">
				Enter an attendee into the log. Badge scanners will work as long as they send a
				<kbd
					class="px-1 inline-block border rounded-sm border-surface-300 dark:border-surface-600 whitespace-nowrap"
				>
					Return &#9166;
				</kbd>
				key press after the badge number.
			</p>

			<Message v-if="!gatekeeper && requirements.length" severity="info">
				Only allows attendees that meet any of:
				<ul class="list-disc ms-5">
					<li v-for="requirement in requirements" :key="requirement">{{ requirement }}</li>
				</ul>
			</Message>

			<div class="flex flex-col gap-2">
				<form @submit.prevent="create" @input="form.clearErrors()">
					<InputGroup>
						<FloatLabel variant="on">
							<InputText
								v-model="form.badge_id"
								ref="input"
								name="badge_id"
								:id="badgeNumberId"
								:invalid="Boolean(form.errors.badge_id || denial)"
								inputmode="numeric"
								required
								:autofocus="!gatekeeper"
								@input="form.clearErrors()"
							/>
							<label :for="badgeNumberId">Badge Number</label>
						</FloatLabel>

						<ResponsiveButton
							:label="gatekeeper ? 'Empower Gatekeeper' : 'Log Attendee'"
							:icon="faUserPlus"
							type="submit"
							:severity="gatekeeper ? 'warn' : 'success'"
							class="shrink-0"
							:loading="form.processing"
							:disabled="form.processing || !form.badge_id"
						/>
					</InputGroup>

					<Message v-if="form.errors.badge_id" size="small" severity="error" variant="simple">
						{{ form.errors.badge_id }}
					</Message>
				</form>

				<!--
					The override controls live outside the badge form so that typing a reason doesn't clear the denial.
					The reason field is never focused automatically, and both the reason field and the Allow Anyway
					button ignore Enter, so a badge scanned while either is focused can't submit an override by accident.
					Keyboard users can still activate the button with Space.
				-->
				<div v-if="denial" class="flex flex-col gap-2">
					<Message size="small" severity="error" variant="simple">
						{{ denial }}
					</Message>

					<template v-if="canOverride">
						<InputGroup>
							<InputText
								v-model="overrideForm.override_reason"
								placeholder="Reason (optional)"
								aria-label="Override reason"
								maxlength="255"
								autocomplete="off"
								:invalid="reasonLooksLikeBadge"
								@keydown.enter.prevent
							/>
							<ResponsiveButton
								label="Allow Anyway"
								:icon="faUserCheck"
								severity="warn"
								class="shrink-0"
								:loading="overrideForm.processing"
								:disabled="overrideForm.processing || reasonLooksLikeBadge"
								@keydown.enter.prevent
								@click="override"
							/>
						</InputGroup>
						<Message v-if="reasonLooksLikeBadge" size="small" severity="warn" variant="simple">
							The reason contains a long number, which is likely a scanned badge. Remove it before
							allowing them in.
						</Message>
					</template>
					<small v-else class="text-muted-color">A manager or admin can let them in anyway.</small>
				</div>
			</div>
		</div>
	</Panel>
</template>

<script setup lang="ts">
import { computed, useId, useTemplateRef } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useRoute } from '@/lib/route';
import type AttendeeLog from '@/data/AttendeeLog';

import { faUserCheck, faUserPlus } from '@fortawesome/free-solid-svg-icons';
import ResponsiveButton from '../Common/ResponsiveButton.vue';
import FullContentHeightPanel from '../Common/FullContentHeightPanel.vue';

import successSoundFile from '@/../audio/success.ogg';
import success2SoundFile from '@/../audio/success2.ogg';
import alertSoundFile from '@/../audio/alert.ogg';

const {
	attendeeLog,
	gatekeeper = false,
	canOverride = false,
} = defineProps<{
	attendeeLog: AttendeeLog;
	gatekeeper?: boolean;
	/** Whether the user can let attendees in that don't meet the log's entry requirements */
	canOverride?: boolean;
}>();

const route = useRoute();

// Human-readable list of the log's entry requirements
const requirements = computed(() => {
	const list: string[] = [];
	if (attendeeLog.allowed_registration_levels?.length) {
		list.push(`Registration level: ${attendeeLog.allowed_registration_levels.join(', ')}`);
	}
	if (attendeeLog.allow_staff) list.push('Staff or above');
	if (attendeeLog.min_volunteer_hours !== null) list.push(`${attendeeLog.min_volunteer_hours}+ volunteer hours`);
	return list;
});
const form = useForm({
	badge_id: '',
	type: gatekeeper ? 'gatekeeper' : 'attendee',
});
const overrideForm = useForm({
	badge_id: '',
	type: 'attendee',
	override: true,
	override_reason: '',
});

// A run of 4 or more digits in the reason is most likely a badge scanned while the reason field had focus
const reasonLooksLikeBadge = computed(() => /\d{4,}/.test(overrideForm.override_reason));
const input = useTemplateRef('input');
const badgeNumberId = useId();

const successSound = new Audio(successSoundFile);
const success2Sound = new Audio(success2SoundFile);
const alertSound = new Audio(alertSoundFile);

const denial = computed(() => form.errors.requirements);

// The override permission is reloaded with every scan so that changes to the log's settings made while someone is
// scanning are picked up without refreshing the page
const reloadProps = ['attendeeLog', 'overriders', 'canOverrideRequirements', 'flash'];

function create() {
	const badgeId = form.badge_id;

	form.put(route('attendee-logs.users.store', attendeeLog.id), {
		replace: true,
		preserveState: true,
		preserveScroll: true,
		only: reloadProps,

		onSuccess() {
			form.reset();
			// @ts-expect-error
			input.value!.$el.focus();

			if (!gatekeeper) successSound.play();
		},
		onError() {
			form.reset();
			// @ts-expect-error
			input.value!.$el.focus();

			// Remember the denied badge so it can be let in with an override
			overrideForm.badge_id = denial.value ? badgeId : '';
			overrideForm.override_reason = '';

			if (gatekeeper) return;

			if (form.errors.badge_id?.includes('already present')) success2Sound.play();
			else alertSound.play();
		},
	});
}

/**
 * Lets the most recently denied attendee in despite the log's entry requirements
 */
function override() {
	if (reasonLooksLikeBadge.value) return;
	overrideForm.put(route('attendee-logs.users.store', attendeeLog.id), {
		replace: true,
		preserveState: true,
		preserveScroll: true,
		only: reloadProps,

		onSuccess() {
			form.clearErrors();
			overrideForm.reset();
			successSound.play();
		},
		onError() {
			form.setError('requirements', Object.values(overrideForm.errors)[0] ?? 'Override failed.');
		},
		onFinish() {
			// @ts-expect-error
			input.value!.$el.focus();
		},
	});
}
</script>
