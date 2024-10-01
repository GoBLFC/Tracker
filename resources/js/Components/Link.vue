<template>
	<Link :href>
		<slot />
	</Link>
</template>

<script setup>
import { computed, inject } from 'vue';
import { Link } from '@inertiajs/vue3';

const route = inject('route');

const props = defineProps({
	to: String,
	url: String,
});

const href = computed(() => (props.to ? route(props.to) : props.url));
</script>
