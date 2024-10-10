<template>
	<template
		:is="as"
		ref="root"
		:data-bs-title="title"
		data-bs-toggle="tooltip"
	>
		<slot />
	</template>
</template>

<script setup>
import { useTemplateRef, watch, onMounted, onUnmounted } from 'vue';
import { Tooltip } from 'bootstrap';

const { title } = defineProps({
	title: { type: String, required: true },
	as: { type: [String, Object], default: 'span' },
});

const root = useTemplateRef('root');
let tooltip = null;

onMounted(() => {
	tooltip = new Tooltip(root.value);
});

onUnmounted(() => {
	tooltip.dispose();
	tooltip = null;
});

watch(
	() => title,
	() => {
		tooltip.dispose();
		tooltip = new Tooltip(root.value);
	},
);
</script>
