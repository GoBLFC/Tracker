<template>
	<div class="card">
		<component :is="headerTag" class="card-header">Create User</component>

		<div class="card-body">
			<form @submit.prevent="createUser" @input="form.clearErrors()">
				<div class="input-group has-validation">
					<label class="input-group-text" :for="inputId">
						Badge Number
					</label>

					<input
						type="text"
						inputmode="numeric"
						pattern="[0-9]+"
						required
						:id="inputId"
						class="form-control"
						:class="{ 'is-invalid': form.errors.badge_id }"
						v-model="form.badge_id"
					/>

					<button
						type="submit"
						class="btn btn-success"
						:disabled="form.processing"
					>
						<FontAwesomeIcon
							:icon="form.processing ? faCircleNotch : faUserPlus"
							:spin="form.processing"
							class="me-1"
						/>
						{{
							form.processing ? "Creating User..." : "Create User"
						}}
					</button>

					<div class="invalid-feedback">
						{{ form.errors.badge_id }}
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
import { inject, useId } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch, faUserPlus } from '@fortawesome/free-solid-svg-icons';

defineProps({
	headerTag: { type: String, default: 'h5' },
});

const route = inject('route');
const form = useForm({ badge_id: '' });
const inputId = useId();

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
