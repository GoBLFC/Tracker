<template>
	<Link :href>
		<slot />
	</Link>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useRoute } from '@/lib/route';

const route = useRoute();

const { to, url } = defineProps<{ to?: string; url?: string }>();

const href = computed(() => (to ? route(to) : url!));
</script>
