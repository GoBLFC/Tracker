<template>
	<Link :href>
		<slot />
	</Link>
</template>

<script setup>
import { computed, inject } from "vue";
import { Link } from "@inertiajs/vue3";

const route = inject("route");

const { to, url } = defineProps({
	to: String,
	url: String,
});

const href = computed(() => (to ? route(to) : url));
</script>
