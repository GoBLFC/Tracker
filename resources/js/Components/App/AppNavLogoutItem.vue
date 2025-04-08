<template>
	<AppNavItem
		to="auth.logout"
		:label="`Sign Out${showHamburger && logoutAt ? ` (${countdown})` : ''}`"
		class="bg-surface-50 dark:bg-surface-900 border-surface-200 dark:border-surface-700"
		:class="{
			'border border-progress': logoutAt && !showHamburger,
		}"
		:style="
			!showHamburger && logoutAt
				? `
						--progress: ${(timeLeft / logoutAfter) * 100}%;
						--progress-radius: 8px;
						--progress-color: rgb(239, 68, 68);
					`
				: ''
		"
		color="text-red-500 hover:text-red-300!"
		:icon="faArrowRightFromBracket"
		:show-text="showHamburger"
		:tooltip-position
	/>
</template>

<script setup lang="ts">
import { useAutoLogout } from '@/lib/logout';

import { faArrowRightFromBracket } from '@fortawesome/free-solid-svg-icons';
import AppNavItem from './AppNavItem.vue';

defineProps<{ showHamburger: boolean; tooltipPosition: 'bottom' | 'right' }>();

const { logoutAt, logoutAfter, timeLeft, countdown } = useAutoLogout();
</script>
