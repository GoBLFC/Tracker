<template>
	<div class="grow flex flex-col justify-center items-center gap-8">
		<Head :title="`${status}: ${title}`" />

		<Link to="tracker.index" :title="$appName">
			<img
				src="@/../img/event-logo.png"
				width="128"
				height="146"
				class="object-scale-down"
				:alt="$appName"
			/>
		</Link>

		<h1 class="text-3xl text-center font-light">
			{{ status }}: {{ title }}
		</h1>

		<div class="flex gap-2">
			<IconButton
				label="Back"
				:icon="faArrowLeft"
				severity="secondary"
				@click="goBack"
			/>

			<IconButton
				:as="Link"
				to="tracker.index"
				label="Home"
				:icon="faHouse"
				severity="secondary"
			/>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

import { Head } from '@inertiajs/vue3';
import { faHouse, faArrowLeft } from '@fortawesome/free-solid-svg-icons';
import Link from '@/Components/Common/Link.vue';
import IconButton from '@/Components/Common/IconButton.vue';

const { status } = defineProps<{ status: number }>();

const title = computed(() => {
	return {
		503: 'Service Unavailable',
		500: 'Internal Server Error',
		404: 'Not Found',
		403: 'Forbidden',
	}[status];
});

/**
 * Navigates to the previous page
 */
function goBack() {
	history.back();
}
</script>
