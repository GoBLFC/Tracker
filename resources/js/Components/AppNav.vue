<template>
	<nav
		class="shrink-0 flex flex-col sm:max-lg:flex-row mx-4 my-4 px-3 py-2 lg:px-2 rounded-lg gap-4 sm:gap-6 lg:gap-8 bg-surface-50 dark:bg-surface-900 overflow-clip lg:overflow-auto select-none"
		:class="{
			'max-h-16': showMenu && !menuOpen,
			'max-h-[32rem]': showMenu && menuOpen,
		}"
		style="transition: max-height ease-in-out 400ms"
	>
		<div class="flex justify-between">
			<img
				src="../../img/blfc-chip.png"
				width="128"
				height="146"
				class="w-auto h-12 lg:w-12 lg:h-auto"
				:title="$appName"
				:alt="$appName"
			/>

			<AppNavItem
				v-if="showMenu"
				wrapper="div"
				:label="menuOpen ? 'Close Menu' : 'Open Menu'"
				:icon="menuOpen ? faClose : faBars"
				:active="menuOpen"
				:tooltip="false"
				:aria-label="menuOpen ? 'Close Menu' : 'Open Menu'"
				:aria-controls="menuId"
				:aria-expanded="menuOpen"
				@click="menuOpen = !menuOpen"
			/>
		</div>

		<div
			:id="menuId"
			class="grow flex flex-col sm:max-lg:flex-row gap-2"
			:aria-hidden="showMenu && !menuOpen"
		>
			<ul class="grow flex flex-col sm:max-lg:flex-row gap-2">
				<AppNavItem
					to="tracker.index"
					label="Home"
					:icon="faHouseCircleCheck"
					:legacy="true"
					:show-text="showMenu"
					:tooltip-position
				/>
				<AppNavItem
					to="management.manage"
					label="Manager Dashboard"
					:icon="faBusinessTime"
					:show-text="showMenu"
					:tooltip-position
				/>
				<AppNavItem
					to="attendee-logs.index"
					label="Attendee Logs"
					:icon="faListCheck"
					:legacy="true"
					:show-text="showMenu"
					:tooltip-position
				/>
				<AppNavItem
					to="admin.users"
					label="Users"
					:icon="faUsers"
					:legacy="true"
					:show-text="showMenu"
					:tooltip-position
				/>
				<AppNavItem
					to="admin.events"
					label="Events"
					:icon="faCalendarDay"
					:legacy="true"
					:show-text="showMenu"
					:tooltip-position
				/>
				<AppNavItem
					to="admin.reports"
					label="Reports"
					:icon="faFileLines"
					:legacy="true"
					:show-text="showMenu"
					:tooltip-position
				/>
				<AppNavItem
					to="admin.site"
					label="Configuration"
					:icon="faScrewdriverWrench"
					:legacy="true"
					:show-text="showMenu"
					:tooltip-position
				/>
			</ul>

			<ul class="flex flex-col sm:max-lg:flex-row gap-1">
				<AppNavItem
					label="About Tracker"
					:icon="faInfoCircle"
					:show-text="showMenu"
					:tooltip-position
				/>
				<AppNavItem
					to="auth.logout.post"
					method="post"
					label="Sign Out"
					color="text-red-500 hover:!text-red-300"
					:icon="faArrowRightFromBracket"
					:show-text="showMenu"
					:tooltip-position
				/>
			</ul>
		</div>
	</nav>
</template>

<script setup lang="ts">
import { ref, toRef, useId } from 'vue';
import {
	faHouseCircleCheck,
	faListCheck,
	faBusinessTime,
	faUsers,
	faCalendarDay,
	faFileLines,
	faScrewdriverWrench,
	faInfoCircle,
	faArrowRightFromBracket,
	faBars,
	faClose,
} from '@fortawesome/free-solid-svg-icons';
import { useBreakpoint, BREAKPOINTS } from '../lib/match-media';
import AppNavItem from './AppNavItem.vue';

const { breakpoint } = useBreakpoint();

const showMenu = toRef(() => BREAKPOINTS[breakpoint.value] < BREAKPOINTS.sm);
const menuOpen = ref(false);
const menuId = useId();

const tooltipPosition = toRef(() => (BREAKPOINTS[breakpoint.value] < BREAKPOINTS.lg ? 'bottom' : 'right'));
</script>
