<template>
	<nav
		class="flex lg:flex-col w-auto h-16 lg:w-16 lg:h-auto mx-4 my-4 px-3 py-2 lg:px-2 rounded-lg items-center gap-6 lg:gap-8 bg-surface-50 dark:bg-surface-900 select-none"
	>
		<img
			src="../../img/blfc-chip.png"
			width="128"
			height="146"
			alt="BLFC Poker Chip Logo"
			class="w-auto h-[90%] lg:w-[90%] lg:h-auto"
		/>

		<ul class="flex lg:flex-col grow gap-2">
			<AppNavItem
				label="Home"
				:icon="faHouseCircleCheck"
				to="tracker.index"
				:legacy="true"
			/>
			<AppNavItem
				label="Attendee Logs"
				:icon="faListCheck"
				to="attendee-logs.index"
				:legacy="true"
			/>
			<AppNavItem
				label="Manager Dashboard"
				:icon="faBusinessTime"
				to="management.manage"
			/>
			<AppNavItem
				label="Users"
				:icon="faUsers"
				to="admin.users"
				:legacy="true"
			/>
			<AppNavItem
				label="Events"
				:icon="faCalendarDay"
				to="admin.events"
				:legacy="true"
			/>
			<AppNavItem
				label="Configuration"
				:icon="faScrewdriverWrench"
				to="admin.site"
				:legacy="true"
			/>
		</ul>

		<ul class="flex lg:flex-col gap-2">
			<AppNavItem label="About Tracker" :icon="faInfoCircle" />
			<AppNavItem
				label="Sign Out"
				:icon="faArrowRightFromBracket"
				to="auth.logout.post"
				method="post"
				color="text-red-500 hover:!text-red-300"
			/>
		</ul>
	</nav>
</template>

<script setup lang="ts">
import {
	faHouseCircleCheck,
	faListCheck,
	faBusinessTime,
	faUsers,
	faCalendarDay,
	faScrewdriverWrench,
	faInfoCircle,
	faArrowRightFromBracket,
} from '@fortawesome/free-solid-svg-icons';
import AppNavItem from './AppNavItem.vue';
</script>
