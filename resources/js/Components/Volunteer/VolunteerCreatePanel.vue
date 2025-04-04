<template>
	<Panel header="Create Volunteer">
		<p class="mb-6">
			If there is a volunteer that hasn't logged in to the system yet, but
			you need to manage data for them, you may add them here using just
			their badge number. Their name and other details will be
			automatically imported when available.
		</p>

		<form @submit.prevent="createUser" @input="form.clearErrors()">
			<InputGroup>
				<FloatLabel variant="on">
					<InputText
						v-model="form.badge_id"
						name="badge_id"
						:id="badgeNumberId"
						:invalid="Boolean(form.errors.badge_id)"
						inputmode="numeric"
						required
						@input="form.clearErrors()"
					/>
					<label :for="badgeNumberId">Badge number</label>
				</FloatLabel>

				<ResponsiveButton
					label="Add Volunteer"
					:icon="faUserPlus"
					type="submit"
					severity="success"
					class="shrink-0"
					:loading="form.processing"
					:disabled="form.processing || !form.badge_id"
				/>
			</InputGroup>

			<Message
				v-if="form.hasErrors"
				size="small"
				severity="error"
				variant="simple"
			>
				{{ form.errors.badge_id }}
			</Message>
		</form>
	</Panel>
</template>

<script setup lang="ts">
import { useId } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useRoute } from '@/lib/route';

import { faUserPlus } from '@fortawesome/free-solid-svg-icons';
import ResponsiveButton from '../Common/ResponsiveButton.vue';

const route = useRoute();
const form = useForm({ badge_id: '' });
const badgeNumberId = useId();

/**
 * Submits the form, creating a user with the given badge ID
 */
function createUser() {
	form.post(route('users.store'), {
		replace: true,
		preserveScroll: true,
		preserveState: true,
		only: ['flash'],
	});
}
</script>
