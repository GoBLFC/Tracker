<template>
	<div class="card mb-4">
		<div class="card-header">{{ actionWord }} for Event</div>

		<div class="card-body">
			<div v-if="events.length > 0" class="input-group">
				<label :for="selectId" class="input-group-text"> Event </label>

				<select
					:id="selectId"
					class="form-control"
					:disabled="loading"
					@change="navigateToEvent"
				>
					<option v-if="!event" value="" selected disabled hidden>
						Select an Event
					</option>

					<option
						v-for="otherEvent of events"
						:key="otherEvent.id"
						:value="otherEvent.id"
						:selected="otherEvent.id === event?.id"
					>
						{{ otherEvent.name }}
					</option>
				</select>
			</div>

			<p v-else-if="isAdmin" class="mb-0">
				There aren't any events yet - you'll need to
				<LegacyLink to="admin.events">create one</LegacyLink> to manage.
			</p>

			<p v-else class="mb-0">There aren't any events yet.</p>
		</div>
	</div>
</template>

<script setup>
import { ref, useId } from 'vue';
import { router } from '@inertiajs/vue3';
import { useUser } from '../lib/user';
import LegacyLink from './LegacyLink.vue';

const { event = null, resolver } = defineProps({
	event: { type: [Object, null], required: true },
	events: { type: Array, required: true },
	resolver: { type: Function, required: true },
	actionWord: { type: String, default: 'Edit' },
});
const emit = defineEmits({
	changing: [String],
	change: [String],
	error: [Array],
});

const selectId = useId();
const { isAdmin } = useUser();

const loading = ref(false);

/**
 * Calls the provided resolver with the event ID from the JS event target's value and navigates to the resulting URL
 * @param {SubmitEvent} evt
 */
function navigateToEvent(evt) {
	const oldEventId = event?.id;
	const newEventId = evt.target.value;
	const { url, data = {}, only, ...options } = resolver(newEventId);

	if (only && !only.includes('event')) only.push('event');

	router.get(url, data, {
		preserveState: true,
		preserveScroll: true,
		only,
		...options,

		onStart() {
			loading.value = true;
			emit('changing', newEventId);
		},
		onSuccess() {
			emit('change', newEventId);
		},
		onError(errors) {
			evt.target.value = oldEventId;
			emit('error', errors);
		},
		onFinish() {
			loading.value = false;
		},
	});
}
</script>
