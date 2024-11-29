<template>
	<LogoutTimer>
		<template #default="{ logoutAt, logoutTime, timeLeft, countdown }">
			<AppNavItem
				to="auth.logout.post"
				method="post"
				:label="`Sign Out${
					showHamburger && logoutAt ? ` (${countdown})` : ''
				}`"
				class="bg-surface-50 dark:bg-surface-900 border-surface-200 dark:border-surface-700"
				:class="{
					'border border-progress': logoutAt && !showHamburger,
				}"
				:style="
					!showHamburger && logoutAt
						? `
								--progress: ${timeLeft / (logoutTime * 10)}%;
								--progress-radius: 8px;
								--progress-color: rgb(239, 68, 68);
							`
						: ''
				"
				color="text-red-500 hover:!text-red-300"
				:icon="faArrowRightFromBracket"
				:show-text="showHamburger"
				:tooltip-position
			/>
		</template>
	</LogoutTimer>
</template>

<script setup lang="ts">
import { faArrowRightFromBracket } from '@fortawesome/free-solid-svg-icons';
import AppNavItem from './AppNavItem.vue';
import LogoutTimer from './LogoutTimer.vue';

defineProps<{ showHamburger: boolean; tooltipPosition: 'bottom' | 'right' }>();
</script>
