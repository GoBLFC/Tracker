<template>
	<div class="h-full overflow-auto flex flex-col relative -z-50">
		<DevBadges v-if="isDevMode" class="mx-2 my-4" />

		<main class="grow p-4 flex flex-col">
			<slot />
		</main>
	</div>
</template>

<script setup lang="ts">
import { useAppSettings } from '../lib/settings';
import DevBadges from '../Components/DevBadges.vue';

const { isDevMode } = useAppSettings();
</script>
