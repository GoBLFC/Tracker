<template>
	<div class="container my-4 my-md-5" :class="{ 'mb-5 pb-5': hasDebugbar }">
		<header class="mb-3 mb-md-4 text-center">
			<LegacyLink to="tracker.index" :title="$appName">
				<img
					src="../../img/blfc-chip.png"
					width="128"
					height="146"
					alt="BLFC Poker Chip Logo"
					class="img-fluid mw-25"
				/>
			</LegacyLink>
		</header>

		<DevBadges v-if="isDevMode" class="mb-3" />

		<main>
			<slot />
		</main>

		<footer>
			<div>
				<LogoutButton v-if="isLoggedIn" class="float-end mb-3" />

				<AboutButton
					class="float-end"
					:class="{ 'me-2': isLoggedIn }"
				/>
			</div>
		</footer>
	</div>
</template>

<script setup lang="ts">
import { useUser } from '../lib/user';
import { useSettings } from '../lib/settings';
import LegacyLink from '../Components/LegacyLink.vue';
import DevBadges from '../Components/DevBadges.vue';
import LogoutButton from '../Components/LogoutButton.vue';
import AboutButton from '../Components/AboutButton.vue';

const { isLoggedIn } = useUser();
const { isDevMode, hasDebugbar } = useSettings();
</script>
