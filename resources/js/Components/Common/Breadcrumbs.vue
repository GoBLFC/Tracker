<template>
	<Breadcrumb :model="trail">
		<template #item="{ item }">
			<Link :to="item.route">{{ item.label }}</Link>
		</template>
	</Breadcrumb>
</template>

<script setup lang="ts">
import { useRoute } from '@/lib/route';

import Link from '../Common/Link.vue';

defineProps<{ trail: Crumb[] }>();

const route = useRoute();

export interface Crumb {
	label: string;
	route?: string | Parameters<typeof route>;
}
</script>
