<template>
	<div class="grow flex flex-col justify-center items-center gap-8">
		<Head :title="`${status}: ${title}`" />

		<Link to="tracker.index" :title="$appName">
			<img
				src="@/../img/event-logo.png"
				width="128"
				height="146"
				alt="Event Logo"
				class="object-scale-down"
			/>
		</Link>

		<h1 class="text-3xl text-center font-light">
			{{ status }}: {{ title }}
		</h1>

		<div class="flex gap-2">
			<Button label="Back" severity="secondary" @click="goBack">
				<template #icon>
					<FontAwesomeIcon :icon="faArrowLeft" />
				</template>
			</Button>

			<Button :as="Link" to="tracker.index" severity="secondary">
				<FontAwesomeIcon :icon="faHouse" />
				Home
			</Button>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

import { Head } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faHouse, faArrowLeft } from '@fortawesome/free-solid-svg-icons';
import Link from '@/Components/Common/Link.vue';

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
