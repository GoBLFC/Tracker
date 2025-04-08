<template>
	<Button :disabled="loading">
		<FontAwesomeIcon
			:icon="loading ? faCircleNotch : icon"
			:class="iconClass"
			:spin="loading"
		/>

		<span
			v-if="$slots.default"
			:class="`tw-sr-only ${breakpoint}:not-sr-only`"
		>
			<slot />
		</span>
		<span v-else :class="`tw-sr-only ${breakpoint}:not-sr-only`">
			{{ label }}
		</span>
	</Button>
</template>

<script setup lang="ts">
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core';
import type { Breakpoint } from '@/lib/media-query';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch } from '@fortawesome/free-solid-svg-icons';

const { breakpoint = 'sm' } = defineProps<{
	label: string;
	icon: IconDefinition;
	loading?: boolean;
	breakpoint?: Breakpoint;
	iconClass?: string | Record<string, boolean>;
}>();
</script>
