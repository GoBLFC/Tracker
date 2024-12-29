<template>
	<div class="grow flex flex-col justify-center items-center gap-4 md:gap-8">
		<img
			src="../../img/event-logo.png"
			class="h-[128px] md:h-[146px] mt-4 object-scale-down"
			:alt="$appName"
		/>

		<Panel
			class="w-full max-w-96"
			:pt="{ content: { class: 'flex flex-col gap-4 mt-2' } }"
		>
			<template #header>
				<h1 class="w-full text-center text-3xl font-light">
					Volunteer Check-In
				</h1>
			</template>

			<Message
				severity="secondary"
				:pt="{
					content: {
						class: 'flex min-h-12 items-center justify-center',
					},
					text: { class: 'w-full text-center' },
				}"
			>
				Welcome! Click below to sign in.
			</Message>

			<Button :as="Link" to="auth.redirect" severity="success">
				Sign In
			</Button>

			<Panel class="mt-8">
				<template #header>
					<h2 class="grow ms-4 text-center text-xl font-thin">
						Quick Sign-in
					</h2>
				</template>

				<template #icons>
					<HelpIcon
						text="Link your Telegram account after signing in normally to get a quick code any time!"
						class="text-lg"
					/>
				</template>

				<form
					class="flex flex-col gap-4"
					@submit.prevent="submitQuickCode()"
				>
					<InputOtp
						v-model="qcForm.code"
						name="code"
						class="flex w-full"
						size="large"
						:disabled="qcForm.processing"
						:invalid="qcForm.hasErrors"
						:pt="{
							pcInputText: {
								root: { class: 'flex-1', required: true },
							},
						}"
						@input="qcForm.clearErrors()"
					/>

					<Message
						v-if="qcForm.hasErrors"
						severity="error"
						variant="simple"
						class="-mt-3"
					>
						{{ qcForm.errors.code }}
					</Message>

					<Button
						type="submit"
						:loading="qcForm.processing"
						:disabled="qcForm.processing || qcForm.code.length != 4"
					>
						Sign In
					</Button>
				</form>
			</Panel>
		</Panel>
	</div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useRoute } from '../lib/route';

import BaseLayout from '../Layouts/BaseLayout.vue';
import NavlessLayout from '../Layouts/NavlessLayout.vue';
import HelpIcon from '../Components/HelpIcon.vue';
import Link from '../Components/Link.vue';

defineOptions({ layout: [BaseLayout, NavlessLayout] });

const route = useRoute();
const qcForm = useForm({ code: '' });

/**
 * Submits the quick code form, logging the user in if it's valid
 */
function submitQuickCode() {
	qcForm.post(route('auth.quickcode.post'), { replace: true });
}
</script>
