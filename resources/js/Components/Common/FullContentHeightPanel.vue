<template>
	<Panel
		class="flex flex-col"
		:pt="{
			contentContainer: { class: 'grow min-h-0' },
			contentWrapper: { class: 'min-w-0' },
			content: { class: 'h-full p-0' },
			...pt,
		}"
	>
		<template #header v-if="$slots.header"><slot name="header" /></template>
		<template #icons v-if="$slots.icons"><slot name="icons" /></template>

		<slot />
	</Panel>
</template>

<script setup lang="ts">
import type { PassThrough } from '@primevue/core';
import type { PanelPassThroughOptions } from 'primevue/panel';

defineProps<{ pt?: PassThrough<PanelPassThroughOptions> }>();
</script>
