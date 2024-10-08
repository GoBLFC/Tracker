<template>
	<a :href>
		<slot />
	</a>
</template>

<script setup>
import { computed, inject } from 'vue';

const route = inject('route');

const { to, url } = defineProps({
	to: String,
	url: String,
});

const href = computed(() => (to ? route(to) : url));
</script>
