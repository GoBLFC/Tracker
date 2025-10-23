<template>
	<InputGroup v-if="events.length > 0">
		<InputGroupAddon @click="focus()" :id="labelId">
			<FontAwesomeIcon :icon="faCalendarDay" />
			<span class="sr-only">Event</span>
		</InputGroupAddon>

		<Select
			ref="select"
			v-model="selectedEvent"
			option-label="name"
			:options
			:placeholder="`Select an event to ${actionWord}`"
			:loading
			:aria-labelledby="labelId"
		>
			<template
				#value="{
					value: event,
					placeholder,
				}: {
					value: TrackerEvent | null | undefined,
					placeholder: string,
				}"
			>
				<div
					v-if="event"
					class="flex grow gap-4 justify-between items-center"
				>
					<span class="truncate">{{ event.name }}</span>
					<Tag
						:value="
							event.id === activeEvent?.id ? 'Active' : 'Inactive'
						"
						:severity="
							event.id === activeEvent?.id
								? 'success'
								: 'secondary'
						"
					/>
				</div>

				<div v-else>{{ placeholder }}</div>
			</template>

			<template #option="{ option: event }: { option: TrackerEvent|'new' }">
				<div v-if="event !== 'new'" class="flex grow gap-6 justify-between items-center">
					<span class="truncate">{{ event.name }}</span>
					<Tag
						:value="
							event.id === activeEvent?.id ? 'Active' : 'Inactive'
						"
						:severity="
							event.id === activeEvent?.id
								? 'success'
								: 'secondary'
						"
					/>
				</div>

				<div v-else>
					<FontAwesomeIcon :icon="faPlus" />
					Create new event
				</div>
			</template>
		</Select>
	</InputGroup>

	<Message v-else-if="isAdmin" class="w-full">
		<p class="text-lg">
			<span class="font-semibold">There aren't any events yet.</span>
			You'll need to
			<Button variant="link" @click="showCreateModal = true">create one</Button> to manage.
		</p>
	</Message>

	<Message v-else class="w-full">
		<p class="text-lg font-semibold">
			There aren't any events to manage yet.
		</p>
	</Message>

	<EventCreateModal v-if="isAdmin" v-model:visible="showCreateModal" />
</template>

<script setup lang="ts">
import { computed, ref, useId, useTemplateRef, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import type { RequestPayload, Errors } from '@inertiajs/core';
import { useAppSettings } from '@/lib/settings';
import { useUser } from '@/lib/user';
import type TrackerEvent from '@/data/Event';
import type { EventId } from '@/data/Event';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCalendarDay, faPlus } from '@fortawesome/free-solid-svg-icons';
import EventCreateModal from '../Dialogs/EventCreateModal.vue';

const {
	event,
	events,
	resolver,
	actionWord = 'edit',
} = defineProps<{
	event: TrackerEvent | null;
	events: TrackerEvent[];
	resolver: EventRequestResolver;
	actionWord?: string;
}>();
const emit = defineEmits<{
	(e: 'changing', eventId: EventId): void;
	(e: 'change', eventId: EventId): void;
	(e: 'error', errors: Errors): void;
}>();

const { activeEvent } = useAppSettings();
const { isAdmin } = useUser();

const selectedEvent = ref<TrackerEvent | 'new' | null>(event);
const loading = ref(false);
const select = useTemplateRef('select');
const labelId = useId();
const showCreateModal = ref(false);
const options = computed<(TrackerEvent | 'new')[]>(() => ['new', ...events]);

// Navigate to the appropriate URL when switching events
watch(selectedEvent, (newEvent, oldEvent) => {
	console.log('ev switch', newEvent, oldEvent);
	if (!newEvent) return;

	if (newEvent === 'new') {
		showCreateModal.value = true;
		selectedEvent.value = oldEvent;
		return;
	}

	// Resolve the router properties to use
	const { url, data = {}, only, ...options } = resolver(newEvent.id);
	if (only && !only.includes('event')) only.push('event');

	// Navigate!
	router.get(url, data, {
		preserveState: true,
		preserveScroll: true,
		only,
		...options,

		onStart() {
			loading.value = true;
			emit('changing', newEvent.id);
		},
		onSuccess() {
			emit('change', newEvent.id);
		},
		onError(errors) {
			selectedEvent.value = oldEvent;
			emit('error', errors);
		},
		onFinish() {
			loading.value = false;
		},
	});
});

/**
 * Sets focus on the dropdown
 */
function focus() {
	// @ts-expect-error
	select.value?.$el?.querySelector('[tabindex]')?.focus();
}

export type EventRequestResolver = (eventId: EventId) => {
	url: string;
	data?: RequestPayload;
	only?: string[];
	[key: string]: unknown;
};
</script>
