<template>
	<a :href>
		<slot />
	</a>
</template>

<script setup>
import { computed, inject } from 'vue';

const route = inject('route');

const props = defineProps({
	to: String,
	url: String,
});

const href = computed(() => (props.to ? route(props.to) : props.url));
</script>
