<template>
	<div class="flex flex-col lg:flex-row h-screen">
		<AppNav />

		<div class="w-full lg:overflow-auto">
			<div class="container mx-auto my-4 md:my-5">
				<DevBadges v-if="isDevMode" class="my-4" />

				<main>
					<slot />
				</main>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { useSettings } from '../lib/settings';
import AppNav from '../Components/AppNav.vue';
import DevBadges from '../Components/DevBadges.vue';

const { isDevMode } = useSettings();
</script>
