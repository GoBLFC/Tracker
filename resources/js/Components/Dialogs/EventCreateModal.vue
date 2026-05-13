<template>
	<Dialog
		modal
		header="Create new event"
		class="w-full sm:w-md mx-2"
		v-model:visible="visibleModel"
		@after-hide="resetIfSuccessful()"
	>
		<form :id="formId" class="mt-1" @submit.prevent="create()">
			<!-- Event properties -->
			<FloatLabel variant="on">
				<InputText
					v-model="form.name"
					name="name"
					:id="nameId"
					:invalid="Boolean(form.errors.name)"
					maxlength="64"
					required
					autofocus
					fluid
					@input="form.clearErrors()"
				/>
				<label :for="nameId">Name</label>
			</FloatLabel>
			<Message v-if="form.errors.name" size="small" severity="error" variant="simple" class="mt-1">
				{{ form.errors.name }}
			</Message>

			<!-- Event cloning -->
			<template v-if="events && events.length > 0">
				<InputGroup class="mt-4">
					<FloatLabel variant="on">
						<Select
							v-model="form.cloneEvent"
							:id="cloneId"
							:options="events"
							option-label="name"
							option-value="id"
							:show-clear="true"
							:invalid="Boolean(form.errors.cloneEvent)"
						/>
						<label :for="cloneId">Copy departments from (optional)</label>
					</FloatLabel>
					<Message v-if="form.errors.cloneEvent" size="small" severity="error" variant="simple" class="mt-1">
						{{ form.errors.cloneEvent }}
					</Message>
				</InputGroup>
			</template>
		</form>

		<template #footer>
			<IconButton
				label="Create"
				:icon="faCalendarPlus"
				variant="text"
				type="submit"
				:form="formId"
				:loading="form.processing"
			/>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { useId } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type TrackerEvent from '@/data/Event';

import { faCalendarPlus } from '@fortawesome/free-solid-svg-icons';
import IconButton from '@/Components/Common/IconButton.vue';

const { events } = defineProps<{ events?: TrackerEvent[] }>();
const visibleModel = defineModel<boolean>('visible', { default: true });

const form = useForm({ name: '', cloneEvent: null });
const formId = useId();
const nameId = useId();
const cloneId = useId();

/**
 * Creates an event with the current input
 */
function create() {
	if (form.processing) return;

	form.post(route('events.store'), {
		onSuccess() {
			setTimeout(() => (visibleModel.value = false), 0);
		},
	});
}

/**
 * Resets the form (since `form.reset()` doesn't seem to work properly here)
 */
function resetIfSuccessful() {
	if (!form.wasSuccessful) return;
	form.name = '';
	form.cloneEvent = null;
}
</script>
