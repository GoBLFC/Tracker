<template>
	<Dialog
		modal
		header="Create new event"
		class="w-full sm:w-[28rem] mx-2"
		v-model:visible="visibleModel"
	>
		<form :id="formId" class="mt-1" @submit.prevent="create()">
			<FloatLabel variant="on">
				<InputText
					v-model="form.name"
					name="name"
					:id="nameId"
					:invalid="Boolean(form.errors.name)"
					required
					autofocus
					fluid
					@input="form.clearErrors()"
				/>
				<label :for="nameId">Name</label>
			</FloatLabel>
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
import { useId, useModel } from 'vue';
import { useForm } from '@inertiajs/vue3';

import { faCalendarPlus } from '@fortawesome/free-solid-svg-icons';
import IconButton from '../Common/IconButton.vue';

const { visible = true } = defineProps<{ visible: boolean }>();
const visibleModel = useModel({ visible: true }, 'visible');

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
