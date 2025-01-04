<template>
	<div class="grow flex flex-col gap-4">
		<Head :title />

		<div
			v-if="events"
			class="flex items-center gap-3"
			:class="{
				'justify-center': events.length === 0,
				'justify-between': events.length > 0,
			}"
		>
			<EventSelector :event :events :resolver :action-word />

			<slot name="status" />
		</div>

		<div v-if="event" class="grow flex flex-col gap-4">
			<slot />
		</div>

		<template v-else>
			<slot name="placeholder">
				<p
					class="grow flex items-center justify-center text-xl text-muted-color"
				>
					Select an event to {{ actionWord }} above.
				</p>
			</slot>
		</template>
	</div>
</template>

<script setup lang="ts">
import type Event from '@/data/Event';

import { Head } from '@inertiajs/vue3';
import EventSelector, { type EventRequestResolver } from '../Manage/EventSelector.vue';

const { event, actionWord = 'manage' } = defineProps<{
	title: string;
	event: Event | null;
	events?: Event[] | null;
	resolver: EventRequestResolver;
	actionWord?: string;
}>();
</script>
