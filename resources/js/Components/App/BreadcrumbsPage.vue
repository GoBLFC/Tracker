<template>
	<div class="grow flex flex-col gap-4">
		<Head :title />

		<Toolbar>
			<template #start>
				<Breadcrumbs :trail :pt="{ root: { class: 'p-0' } }" />
			</template>

			<template #end v-if="$slots.icons">
				<slot name="icons" />
			</template>
		</Toolbar>

		<slot />
	</div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Breadcrumbs, { type Crumb } from '../Common/Breadcrumbs.vue';

defineProps<{
	title: string;
	trail: Crumb[];
}>();
</script>
