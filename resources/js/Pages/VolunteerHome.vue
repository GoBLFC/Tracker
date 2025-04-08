<template>
	<div
		class="grow mx-auto flex flex-col justify-center gap-4"
		:class="{
			'container max-w-[80rem]': activeEvent,
			'w-fit': !activeEvent,
		}"
	>
		<div class="flex justify-between items-end gap-2">
			<h1 class="text-3xl font-light">
				Welcome,
				<span class="font-normal"> {{ displayName }} </span>!
			</h1>

			<ResponsiveButton
				v-if="!activeEvent || hasTelegram"
				:icon="faTelegram"
				label="Link Telegram"
				severity="info"
				size="small"
				breakpoint="lg"
				icon-class="text-lg"
				@click="isTelegramDialogVisible = true"
			/>
		</div>

		<template v-if="activeEvent">
			<Message
				v-if="!hasTelegram"
				severity="info"
				:pt="{ text: { class: 'w-full' } }"
			>
				<div class="w-full flex justify-between items-center gap-4">
					<p>
						Link your Telegram account to get quick sign-in codes,
						easily check your shift and reward status from anywhere,
						and receive helpful notifications!
					</p>

					<ResponsiveButton
						:icon="faTelegram"
						label="Link Telegram"
						severity="info"
						breakpoint="lg"
						iconClass="text-lg"
						@click="isTelegramDialogVisible = true"
					/>
				</div>
			</Message>

			<Panel header="Shift Entry">
				<div class="flex flex-col flex-wrap lg:flex-row gap-4">
					<Message
						v-if="kioskRequired"
						severity="warn"
						class="w-full"
					>
						You must visit an authorized volunteer kiosk to check
						{{ ongoing ? "out" : "in" }}.
					</Message>

					<Message :severity="ongoing ? 'success' : 'secondary'">
						{{
							ongoing
								? "You're checked in for a shift."
								: "You're not checked in right now."
						}}
					</Message>

					<form @submit.prevent="checkInOrOut" class="flex-1">
						<InputGroup>
							<DepartmentPicker
								v-model="department"
								:departments
								:disabled="ongoing || kioskRequired"
							/>

							<ResponsiveButton
								:label="ongoing ? 'Check Out' : 'Check In'"
								:icon="
									ongoing
										? faArrowRightFromBracket
										: faArrowRightToBracket
								"
								:severity="ongoing ? 'warn' : 'success'"
								:loading="loading"
								:disabled="
									loading ||
									(!ongoing && !department) ||
									kioskRequired
								"
								type="submit"
								class="shrink-0"
							/>
						</InputGroup>
					</form>
				</div>
			</Panel>

			<Panel header="Time Stats">
				<VolunteerTimeStats :time="volunteer.time" :now />
			</Panel>
		</template>

		<div v-else class="flex flex-col gap-4">
			<Message severity="secondary" size="large">
				There isn't an event running right now, so there's nothing to
				track your time for. Check back later!
			</Message>
		</div>

		<TelegramModal
			v-model:visible="isTelegramDialogVisible"
			:setup-url="telegramSetupUrl"
		/>

		<SwalSuccessDialog
			ref="success-dialog"
			:title="`Checked ${ongoing ? 'In' : 'Out'}`"
		/>
	</div>
</template>

<script setup lang="ts">
import { computed, ref, useTemplateRef } from 'vue';
import { router, usePoll } from '@inertiajs/vue3';
import { useAppSettings } from '@/lib/settings';
import { useUser } from '@/lib/user';
import { useNow } from '@/lib/time';
import { useRoute } from '@/lib/route';
import { useToast } from '@/lib/toast';
import type Volunteer from '@/data/Volunteer';
import type Department from '@/data/Department';

import { faArrowRightFromBracket, faArrowRightToBracket } from '@fortawesome/free-solid-svg-icons';
import { faTelegram } from '@fortawesome/free-brands-svg-icons';
import VolunteerTimeStats from '@/Components/Volunteer/VolunteerTimeStats.vue';
import DepartmentPicker from '@/Components/Department/DepartmentPicker.vue';
import TelegramModal from '@/Components/Dialogs/TelegramModal.vue';
import SwalSuccessDialog from '@/Components/Dialogs/SwalSuccessDialog.vue';
import ResponsiveButton from '@/Components/Common/ResponsiveButton.vue';

const { volunteer, departments } = defineProps<{
	volunteer: Volunteer;
	departments: Department[];
	telegramSetupUrl: string;
	hasTelegram: boolean;
}>();

const { activeEvent, isKiosk, isDevMode } = useAppSettings();
const { displayName, isStaff } = useUser();
const { now } = useNow();
const route = useRoute();
const toast = useToast();
usePoll(15000, { only: ['volunteer', 'hasTelegram'] });

const ongoing = computed(() => volunteer.time.entries.find((entry) => !entry.stop));
const department = ref(ongoing.value?.department);
const kioskRequired = computed(() => !isKiosk.value && !isDevMode.value && !isStaff.value);
const loading = ref(false);
const isTelegramDialogVisible = ref(false);
const successDialog = useTemplateRef('success-dialog');

/**
 * Sends a request to check the user in or out
 */
function checkInOrOut() {
	if (kioskRequired.value) return;

	const checkingIn = !ongoing.value;
	router.post(
		route(`tracker.check${checkingIn ? 'in' : 'out'}.post`),
		{
			department_id: department.value!.id,
		},
		{
			replace: true,
			preserveScroll: true,
			preserveState: true,

			onStart() {
				loading.value = true;
			},
			onSuccess() {
				successDialog.value!.show();
			},
			onError(errors) {
				const msg = `Failed to check ${checkingIn ? 'in' : 'out'}.`;
				console.error(msg, errors);
				toast.error(msg);
			},
			onFinish() {
				loading.value = false;
			},
		},
	);
}
</script>
