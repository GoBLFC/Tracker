<template>
	<Button>
		<FontAwesomeIcon :icon :class="iconClass" />

		<span
			v-if="$slots.default"
			:class="`sr-only ${breakpoint}:not-sr-only`"
		>
			<slot />
		</span>
		<span v-else :class="`sr-only ${breakpoint}:not-sr-only`">
			{{ label }}
		</span>
	</Button>
</template>

<script setup lang="ts">
import type { Breakpoint } from '../lib/media-query';
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const { breakpoint = 'sm' } = defineProps<{
	label: string;
	icon: IconDefinition;
	loading?: boolean;
	breakpoint?: Breakpoint;
	iconClass?: string | Record<string, boolean>;
}>();
</script>
