<template>
	<Panel header="Create Attendee Log">
		<form @submit.prevent="createLog" @input="form.clearErrors()">
			<InputGroup>
				<FloatLabel variant="on">
					<InputText
						v-model="form.name"
						name="name"
						:id="nameId"
						:invalid="Boolean(form.errors.name)"
						required
						@input="form.clearErrors()"
					/>
					<label :for="nameId">Name</label>
				</FloatLabel>

				<ResponsiveButton
					label="Create"
					:icon="faPlus"
					type="submit"
					severity="success"
					class="shrink-0"
					:loading="form.processing"
					:disabled="form.processing || !form.name"
				/>
			</InputGroup>

			<Message
				v-if="form.hasErrors"
				size="small"
				severity="error"
				variant="simple"
			>
				{{ form.errors.name }}
			</Message>
		</form>
	</Panel>
</template>

<script setup lang="ts">
import { useId } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useRoute } from '@/lib/route';
import type Event from '@/data/Event';

import { faPlus } from '@fortawesome/free-solid-svg-icons';
import ResponsiveButton from '../Common/ResponsiveButton.vue';

const { event } = defineProps<{ event: Event }>();

const route = useRoute();
const form = useForm({ name: '' });
const nameId = useId();

/**
 * Submits the form, creating an attendee log with the given name
 */
function createLog() {
	form.post(route('events.attendee-logs.store', event.id), {
		replace: true,
		preserveScroll: true,
		preserveState: true,
		only: ['attendeeLogs', 'flash'],
		onSuccess() {
			form.reset();
		},
	});
}
</script>
