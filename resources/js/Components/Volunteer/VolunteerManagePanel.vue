<template>
	<Panel
		v-if="volunteer"
		ref="panel"
		:pt="{
			header: { class: 'items-start' },
		}"
	>
		<template #header>
			<span class="p-panel-title">
				<VolunteerName :volunteer show-id />
			</span>
		</template>

		<template #icons>
			<IconButton
				ref="close-btn"
				:icon="faXmark"
				severity="secondary"
				rounded
				text
				aria-label="Close"
				@click="emit('close')"
			/>
		</template>

		<div class="flex flex-col gap-4">
			<VolunteerTimeStats :time="volunteer.time" :now />

			<VolunteerClaimsPanel v-model="volunteer" :rewards :read-only />

			<VolunteerTimeEntriesPanel v-model="volunteer" :now :read-only />

			<VolunteerTimeAddPanel
				v-if="!readOnly"
				v-model="volunteer"
				:event
				:departments
			/>
		</div>
	</Panel>
</template>

<script setup lang="ts">
import { useTemplateRef } from 'vue';
import { isElementInView } from '@/lib/util';
import type Volunteer from '@/data/Volunteer';
import type Event from '@/data/Event';
import type Reward from '@/data/Reward';
import type Department from '@/data/Department';

import { faXmark } from '@fortawesome/free-solid-svg-icons';
import VolunteerTimeStats from './VolunteerTimeStats.vue';
import VolunteerClaimsPanel from './VolunteerClaimsPanel.vue';
import VolunteerTimeEntriesPanel from './VolunteerTimeEntriesPanel.vue';
import VolunteerTimeAddPanel from './VolunteerTimeAddPanel.vue';
import VolunteerName from './VolunteerName.vue';
import IconButton from '../Common/IconButton.vue';

defineExpose({ attention });
const { readOnly = false } = defineProps<{
	event: Event;
	rewards: Reward[];
	departments: Department[];
	now?: number;
	readOnly?: boolean;
}>();
const emit = defineEmits<(e: 'close') => void>();
const volunteer = defineModel<Volunteer>();

const panel = useTemplateRef('panel');
const closeBtn = useTemplateRef('close-btn');

/**
 * Pulses the panel's border and scrolls the viewport to it
 */
function attention() {
	// Pulse the panel border
	// panel.value!.classList.remove("transition-border");
	// panel.value!.classList.replace("border-info", "border-info-subtle");
	// setTimeout(() => {
	// 	panel.value!.classList.add("transition-border");
	// 	panel.value!.classList.replace("border-info-subtle", "border-info");
	// 	setTimeout(() => {
	// 		panel.value!.classList.replace("border-info", "border-info-subtle");
	// 	}, 500);
	// }, 0);

	// Scroll to the panel if the header isn't in view
	if (!isElementInView(closeBtn.value!.$el)) {
		// @ts-expect-error
		panel.value!.$el.scrollIntoView({
			block:
				// @ts-expect-error
				panel.value!.$el.clientHeight < window.innerHeight ? 'center' : 'start',
		});
	}
}
</script>
