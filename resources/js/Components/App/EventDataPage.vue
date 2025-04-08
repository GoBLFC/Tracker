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
			<EventNavigator :event :events :resolver :action-word />

			<div
				v-if="readOnly"
				class="text-2xl text-muted-color"
				v-tooltip.left="'Read-only'"
			>
				<FontAwesomeIcon :icon="faEye" />
				<span class="tw-sr-only">Read-only</span>
			</div>
		</div>

		<div v-if="event" class="grow flex flex-col gap-4">
			<slot :event :read-only />
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
import { toRef } from 'vue';
import { useReadOnly } from '@/lib/readonly';
import type Event from '@/data/Event';

import { Head } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEye } from '@fortawesome/free-solid-svg-icons';
import EventNavigator, { type EventRequestResolver } from '../Event/EventNavigator.vue';

const { event, actionWord = 'manage' } = defineProps<{
	title: string;
	event: Event | null;
	events?: Event[] | null;
	resolver: EventRequestResolver;
	actionWord?: string;
}>();

const isEventReadOnly = useReadOnly();

const readOnly = toRef(() => Boolean(event && isEventReadOnly(event)));
</script>
