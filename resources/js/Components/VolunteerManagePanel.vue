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
			<Button
				ref="close-btn"
				severity="secondary"
				rounded
				text
				aria-label="Close"
				@click="emit('close')"
			>
				<template #icon>
					<FontAwesomeIcon :icon="faXmark" />
				</template>
			</Button>
		</template>

		<div class="flex flex-col">
			<VolunteerTimeStats
				class="mb-4"
				:stats="volunteer.stats"
				:ongoing
				:now
			/>

			<VolunteerClaimsPanel v-model="volunteer" class="mb-4" :rewards />

			<VolunteerTimeEntriesPanel v-model="volunteer" class="mb-4" :now />

			<VolunteerTimeAddPanel v-model="volunteer" :event :departments />
		</div>
	</Panel>
</template>

<script setup lang="ts">
import { computed, useTemplateRef } from 'vue';
import { isElementInView } from '../lib/util';
import type Volunteer from '../data/Volunteer';
import type Event from '../data/Event';
import type Reward from '../data/Reward';
import type Department from '../data/Department';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faXmark } from '@fortawesome/free-solid-svg-icons';
import VolunteerTimeStats from './VolunteerTimeStats.vue';
import VolunteerClaimsPanel from './VolunteerClaimsPanel.vue';
import VolunteerTimeEntriesPanel from './VolunteerTimeEntriesPanel.vue';
import VolunteerTimeAddPanel from './VolunteerTimeAddPanel.vue';
import VolunteerName from './VolunteerName.vue';

defineExpose({ attention });
defineProps<{
	event: Event;
	rewards: Reward[];
	departments: Department[];
	now?: number;
}>();
const emit = defineEmits<(e: 'close') => void>();
const volunteer = defineModel<Volunteer>();

const panel = useTemplateRef('panel');
const closeBtn = useTemplateRef('close-btn');

const ongoing = computed(() => volunteer.value!.stats.entries.find((entry) => !entry.stop));

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
		panel.value!.$el.scrollIntoView({
			block: panel.value!.$el.clientHeight < window.innerHeight ? 'center' : 'start',
		});
	}
}
</script>
