<template>
	<div class="card border-info-subtle" ref="card">
		<div
			class="card-header d-flex align-items-center justify-content-between"
			ref="header"
		>
			<div></div>

			<h5 class="m-0">
				{{ volunteer.user.badge_name ?? volunteer.user.username }}
				(#{{ volunteer.user.badge_id }})
			</h5>

			<button
				type="button"
				class="btn btn-link btn-lg text-info p-0"
				title="Close"
				aria-label="Close"
				@click="emit('close')"
			>
				<FontAwesomeIcon :icon="faXmark" />
			</button>
		</div>

		<div class="card-body">
			<VolunteerTimeStats :stats="volunteer.stats" :ongoing :now />

			<VolunteerClaimsCard v-model="volunteer" class="mb-3" :rewards />

			<VolunteerTimeEntriesCard class="mb-3" v-model="volunteer" :now />

			<VolunteerTimeAddCard v-model="volunteer" :event :departments />
		</div>
	</div>
</template>

<script setup>
import { computed, useTemplateRef } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faXmark } from '@fortawesome/free-solid-svg-icons';
import { isElementInView } from '../lib/util';
import VolunteerTimeStats from './VolunteerTimeStats.vue';
import VolunteerClaimsCard from './VolunteerClaimsCard.vue';
import VolunteerTimeEntriesCard from './VolunteerTimeEntriesCard.vue';
import VolunteerTimeAddCard from './VolunteerTimeAddCard.vue';

defineExpose({ attention });
defineProps({
	event: { type: Object, required: true },
	rewards: { type: Array, required: true },
	departments: { type: Array, required: true },
	now: { type: Number, required: false },
});
const emit = defineEmits({ close: [] });
const volunteer = defineModel();

const card = useTemplateRef('card');
const header = useTemplateRef('header');

const ongoing = computed(() => volunteer.value.stats.entries.find((entry) => !entry.stop));

/**
 * Pulses the card's border and scrolls the viewport to it
 */
function attention() {
	// Pulse the card border
	card.value.classList.remove('transition-border');
	card.value.classList.replace('border-info', 'border-info-subtle');
	setTimeout(() => {
		card.value.classList.add('transition-border');
		card.value.classList.replace('border-info-subtle', 'border-info');
		setTimeout(() => {
			card.value.classList.replace('border-info', 'border-info-subtle');
		}, 500);
	}, 0);

	// Scroll to the card if the header isn't in view
	if (!isElementInView(header.value)) {
		card.value.scrollIntoView({
			block: card.value.clientHeight < window.innerHeight ? 'center' : 'start',
		});
	}
}
</script>
