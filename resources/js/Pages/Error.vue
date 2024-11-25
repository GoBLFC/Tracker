<template>
	<div
		class="d-flex vh-100 flex-column justify-content-center align-items-center"
	>
		<Head :title="`${status}: ${title}`" />

		<header class="mb-3 mb-md-4">
			<LegacyLink to="tracker.index" :title="$appName">
				<img
					src="../../img/event-logo.png"
					width="128"
					height="146"
					alt="Event Logo"
					class="img-fluid mw-25"
				/>
			</LegacyLink>
		</header>

		<main
			class="d-flex flex-column justify-content-center align-items-center"
		>
			<h1 class="mb-4">{{ status }}: {{ title }}</h1>

			<nav class="d-flex gap-2">
				<button type="button" class="btn btn-primary" @click="goBack">
					<FontAwesomeIcon :icon="faArrowLeft" class="me-1" />
					Back
				</button>

				<LegacyLink
					to="tracker.index"
					role="button"
					class="btn btn-primary"
				>
					<FontAwesomeIcon :icon="faHouse" class="me-1" />
					Home
				</LegacyLink>
			</nav>
		</main>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faHouse, faArrowLeft } from '@fortawesome/free-solid-svg-icons';
import LegacyLink from '../Components/LegacyLink.vue';

defineOptions({ layout: [] });
const props = defineProps<{ status: number }>();

const title = computed(() => {
	return {
		503: 'Service Unavailable',
		500: 'Internal Server Error',
		404: 'Not Found',
		403: 'Forbidden',
	}[props.status];
});

/**
 * Navigates to the previous page
 */
function goBack() {
	history.back();
}
</script>
