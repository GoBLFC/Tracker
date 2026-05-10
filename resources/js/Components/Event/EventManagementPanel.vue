<template>
	<Panel header="Edit event">
		<div class="mt-1 flex gap-2 md:gap-4 items-center">
			<form class="w-full" @submit.prevent="rename()">
				<InputGroup>
					<FloatLabel variant="on">
						<InputText
							v-model="renameForm.name"
							name="name"
							:invalid="Boolean(renameForm.errors.name)"
							required
							maxlength="64"
							:readonly
							@input="renameForm.clearErrors()"
						/>
						<label :for="nameId">Name</label>
					</FloatLabel>
					<IconButton
						v-if="!readonly"
						label="Rename"
						type="submit"
						:icon="faSave"
						:disabled="renameForm.processing || renameForm.hasErrors || renameForm.name === event.name"
					/>
				</InputGroup>

				<Message
					v-if="Boolean(renameForm.errors.name)"
					size="small"
					severity="error"
					variant="simple"
					class="mt-1"
					>{{ renameForm.errors.name }}</Message
				>
			</form>

			<ButtonGroup v-if="!readonly" aria-label="Event actions">
				<IconButton
					:icon="faCalendarCheck"
					variant="text"
					:loading="activateRequest.processing.value"
					:disabled="activateRequest.processing.value || deleteRequest.processing.value || event.id === activeEvent?.id"
					v-tooltip.bottom="'Make active'"
					@click="activate()"
				/>
				<IconButton
					:icon="faTrash"
					severity="danger"
					variant="text"
					:loading="deleteRequest.processing.value"
					:disabled="deleteRequest.processing.value || activateRequest.processing.value"
					v-tooltip.bottom="'Delete'"
					@click="del()"
				/>
			</ButtonGroup>
		</div>
	</Panel>
</template>

<script setup lang="ts">
import { useId, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useInertiaRequest } from '@/lib/request';
import { useRoute } from '@/lib/route';
import { useConfirm } from '@/lib/confirm';
import { useAppSettings } from '@/lib/settings';
import type TrackerEvent from '@/data/Event';

import { faCalendarCheck, faSave, faTrash } from '@fortawesome/free-solid-svg-icons';
import IconButton from '../Common/IconButton.vue';

const { event, readonly = false } = defineProps<{ event: TrackerEvent; readonly?: boolean }>();

const route = useRoute();
const { confirm } = useConfirm();
const { activeEvent } = useAppSettings();

watch(
	() => event,
	(newEvent) => {
		renameForm.name = newEvent.name;
	},
);

const renameForm = useForm({ name: event.name });
const nameId = useId();

/**
 * Renames the event
 */
function rename() {
	renameForm.patch(route('events.update', event.id), {
		replace: true,
		preserveState: true,
		preserveScroll: true,
		only: ['event', 'events', 'flash'],
		onError(err) {
			console.error(`Error renaming event ${event.id}`, err);
		},
	});
}

const activateRequest = useInertiaRequest();

/**
 * Makes the event the active event
 */
async function activate() {
	if (!(await confirm(`Make event active?`, { accept: { label: 'Activate' } }))) return;

	activateRequest.patch(
		['settings.update', 'active-event'],
		{ value: event.id },
		{
			replace: true,
			only: ['activeEvent', 'flash'],
			preserveState: true,
			preserveScroll: true,
			onError(err) {
				console.error(`Error activating event ${event.id}`, err);
			},
		},
	);
}

const deleteRequest = useInertiaRequest();

/**
 * Deletes the event
 */
async function del() {
	const confirmed = await confirm(`Delete event?`, {
		accept: { label: 'Delete', severity: 'danger' },
	});
	if (!confirmed) return;

	deleteRequest.del(['events.destroy', event.id], {
		onError(err) {
			console.error(`Error deleting event ${event.id}`, err);
		},
	});
}
</script>
