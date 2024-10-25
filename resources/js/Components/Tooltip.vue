<template>
	<component
		:is="as"
		ref="root"
		:data-bs-title="title"
		data-bs-toggle="tooltip"
	>
		<slot />
	</component>
</template>

<script setup lang="ts">
import { useTemplateRef, watch, onMounted, onUnmounted } from 'vue';
import { Tooltip } from 'bootstrap';

const { title, as = 'span' } = defineProps<{
	title: string;
	as?: string | object;
}>();

const root = useTemplateRef('root');

let tooltip: Tooltip | null = null;

onMounted(() => {
	tooltip = new Tooltip(root.value as Element);
});

onUnmounted(() => {
	tooltip!.dispose();
	tooltip = null;
});

watch(
	() => title,
	() => {
		tooltip?.dispose();
		tooltip = new Tooltip(root.value as Element);
	},
);
</script>
