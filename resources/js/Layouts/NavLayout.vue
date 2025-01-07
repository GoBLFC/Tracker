<template>
	<div class="h-full flex flex-col lg:flex-row relative -z-50">
		<AppNav />

		<div class="grow lg:overflow-auto flex flex-col" scroll-region>
			<DevBadges v-if="isDevMode" class="mx-2 mb-4 lg:mb-0 lg:mt-4" />

			<main class="grow p-4 pt-0 lg:ps-0 lg:pt-4 flex flex-col">
				<slot />
			</main>
		</div>
	</div>
</template>

<script setup lang="ts">
import { useAppSettings } from '@/lib/settings';

import AppNav from '@/Components/App/AppNav.vue';
import DevBadges from '@/Components/App/DevBadges.vue';

const { isDevMode } = useAppSettings();
</script>
