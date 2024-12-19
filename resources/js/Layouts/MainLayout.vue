<template>
	<div class="h-full flex flex-col lg:flex-row relative -z-50">
		<AppNav />

		<div class="w-full h-full lg:overflow-auto">
			<DevBadges v-if="isDevMode" class="mx-2 mb-4 lg:mb-0 lg:mt-4" />

			<main class="h-full px-4 py-4 pt-0 lg:ps-0 lg:pt-4">
				<slot />
			</main>
		</div>
	</div>
</template>

<script setup lang="ts">
import { useAppSettings } from '../lib/settings';
import AppNav from '../Components/AppNav.vue';
import DevBadges from '../Components/DevBadges.vue';

const { isDevMode } = useAppSettings();
</script>
