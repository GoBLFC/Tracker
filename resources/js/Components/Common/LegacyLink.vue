<template>
	<a :href>
		<slot />
	</a>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from '@/lib/route';

const route = useRoute();

const { to, url } = defineProps<{
	to?: string | Parameters<typeof route>;
	url?: string;
}>();

const href = computed(() => (to ? (Array.isArray(to) ? route(...to) : route(to)) : url!));
</script>
