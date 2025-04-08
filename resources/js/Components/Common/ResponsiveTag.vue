<template>
	<Tag
		:class="{
			'w-8 py-2': !isBreakpointReached,
			'cursor-help': tooltipVal,
		}"
		v-tooltip.bottom="tooltipVal"
	>
		<FontAwesomeIcon :icon />

		<span :class="`tw-sr-only ${breakpoint}:not-sr-only`">
			{{ label }}
		</span>
	</Tag>
</template>

<script setup lang="ts">
import { toRef } from 'vue';
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core';
import { BREAKPOINTS, useBreakpoints, type Breakpoint } from '@/lib/media-query';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const {
	label,
	tooltip,
	breakpoint = 'md',
} = defineProps<{
	label: string;
	tooltip?: string;
	icon: IconDefinition;
	breakpoint?: Breakpoint;
}>();

const { breakpoint: currentBreakpoint } = useBreakpoints();

const isBreakpointReached = toRef(() => BREAKPOINTS[currentBreakpoint.value] >= BREAKPOINTS[breakpoint]);
const tooltipVal = toRef(() => {
	if (tooltip) return tooltip;
	if (!isBreakpointReached.value) return label;
	return undefined;
});
</script>
