<template>
	<Dialog modal header="Create new event" class="w-full sm:w-md mx-2" v-model:visible="visibleModel">
		<form :id="formId" class="mt-1" @submit.prevent="create()">
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
		</form>

		<template #footer>
			<IconButton
				label="Create"
				:icon="faCalendarPlus"
				variant="text"
				type="submit"
				:form="formId"
				:loading="form.processing"
				:disabled="form.processing"
			/>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { useId } from 'vue';
import { useForm } from '@inertiajs/vue3';

import { faCalendarPlus } from '@fortawesome/free-solid-svg-icons';
import IconButton from '../Common/IconButton.vue';

const visibleModel = defineModel<boolean>('visible', { default: true });

const form = useForm({ name: '' });
const formId = useId();
const nameId = useId();

function create() {
	form.post(route('events.store'), {
		onSuccess() {
			visibleModel.value = false;
		},
	});
}
</script>
