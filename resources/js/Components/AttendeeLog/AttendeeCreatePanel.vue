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

			<form @submit.prevent="create" @input="form.clearErrors()">
				<InputGroup>
					<FloatLabel variant="on">
						<InputText
							v-model="form.badge_id"
							ref="input"
							name="badge_id"
							:id="badgeNumberId"
							:invalid="form.hasErrors"
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

				<Message v-if="form.hasErrors" size="small" severity="error" variant="simple">
					{{ form.errors.badge_id ?? form.errors.requirements }}
				</Message>
			</form>
		</div>
	</Panel>
</template>

<script setup lang="ts">
import { computed, useId, useTemplateRef } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useRoute } from '@/lib/route';
import type AttendeeLog from '@/data/AttendeeLog';

import { faUserPlus } from '@fortawesome/free-solid-svg-icons';
import ResponsiveButton from '../Common/ResponsiveButton.vue';
import FullContentHeightPanel from '../Common/FullContentHeightPanel.vue';

import successSoundFile from '@/../audio/success.ogg';
import success2SoundFile from '@/../audio/success2.ogg';
import alertSoundFile from '@/../audio/alert.ogg';

const { attendeeLog, gatekeeper = false } = defineProps<{
	attendeeLog: AttendeeLog;
	gatekeeper?: boolean;
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
const input = useTemplateRef('input');
const badgeNumberId = useId();

const successSound = new Audio(successSoundFile);
const success2Sound = new Audio(success2SoundFile);
const alertSound = new Audio(alertSoundFile);

function create() {
	form.put(route('attendee-logs.users.store', attendeeLog.id), {
		replace: true,
		preserveState: true,
		preserveScroll: true,
		only: ['attendeeLog', 'flash'],

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

			if (gatekeeper) return;

			if (form.errors.badge_id?.includes('already present')) success2Sound.play();
			else alertSound.play();
		},
	});
}
</script>
