<template>
	<div
		:id
		class="input-group datetime-picker"
		data-td-target-input="nearest"
		data-td-target-toggle="nearest"
		ref="root"
	>
		<input
			:id="`${id}-input`"
			type="text"
			class="form-control"
			:data-td-target="`#${id}`"
			v-bind="$attrs"
		/>
		<span
			class="input-group-text"
			:data-td-target="`#${id}`"
			data-td-toggle="datetimepicker"
		>
			<FontAwesomeIcon :icon="faCalendar" />
		</span>
	</div>

	<div class="d-none">
		<FontAwesomeIcon :icon="faXmark" :id="`${id}-close`" />
		<FontAwesomeIcon :icon="faClock" :id="`${id}-time`" />
		<FontAwesomeIcon :icon="faCalendar" :id="`${id}-date`" />
		<FontAwesomeIcon :icon="faArrowUp" :id="`${id}-up`" />
		<FontAwesomeIcon :icon="faArrowDown" :id="`${id}-down`" />
		<FontAwesomeIcon :icon="faChevronRight" :id="`${id}-next`" />
		<FontAwesomeIcon :icon="faChevronLeft" :id="`${id}-previous`" />
		<FontAwesomeIcon :icon="faCalendarCheck" :id="`${id}-today`" />
		<FontAwesomeIcon :icon="faTrash" :id="`${id}-clear`" />
		<FontAwesomeIcon :icon="faXmark" :id="`${id}-close`" />
	</div>
</template>

<script setup lang="ts">
import { useId, useTemplateRef, onMounted, onUnmounted } from 'vue';
import { TempusDominus, Namespace as TD } from '@eonasdan/tempus-dominus';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
	faCalendar,
	faClock,
	faArrowUp,
	faArrowDown,
	faChevronRight,
	faChevronLeft,
	faCalendarCheck,
	faTrash,
	faXmark,
} from '@fortawesome/free-solid-svg-icons';

defineOptions({ inheritAttrs: false });
const { format = 'yyyy-MM-dd hh:mm:ss T' } = defineProps<{ format?: string }>();
const datetime = defineModel<Date | null>();

const id = useId();
const root = useTemplateRef('root');

let picker: TempusDominus | null = null;

onMounted(() => {
	picker = new TempusDominus(root.value!, {
		localization: { format },
		display: {
			theme: 'dark',
			icons: {
				type: 'sprites',
				time: `#${id}-time`,
				date: `#${id}-date`,
				up: `#${id}-up`,
				down: `#${id}-down`,
				next: `#${id}-next`,
				previous: `#${id}-previous`,
				today: `#${id}-today`,
				clear: `#${id}-clear`,
				close: `#${id}-close`,
			},
		},
	});

	picker.subscribe(TD.events.change, (evt) => {
		datetime.value = evt.date;
	});
});

onUnmounted(() => {
	picker!.dispose();
	picker = null;
});
</script>
