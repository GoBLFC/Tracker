<template>
	<nav
		class="shrink-0 flex flex-col sm:max-lg:flex-row mx-4 my-4 px-3 py-2 lg:px-2 rounded-lg gap-4 sm:gap-6 lg:gap-8 bg-surface-50 dark:bg-surface-900 overflow-clip lg:overflow-auto select-none -z-20"
		:class="{
			'max-h-16': showHamburger && !hamburgerOpen,
			'max-h-[32rem]': showHamburger && hamburgerOpen,
		}"
		style="transition: max-height ease-in-out 400ms"
	>
		<div class="flex justify-between">
			<img
				src="@/img/event-logo.png"
				width="128"
				height="146"
				class="w-auto h-12 lg:w-12 lg:h-auto"
				:title="$appName"
				:alt="$appName"
			/>

			<AppNavItem
				v-if="showHamburger"
				wrapper="div"
				:label="hamburgerOpen ? 'Close Menu' : 'Open Menu'"
				:icon="hamburgerOpen ? faClose : faBars"
				:active="hamburgerOpen"
				:tooltip="false"
				:aria-label="hamburgerOpen ? 'Close Menu' : 'Open Menu'"
				:aria-controls="menuId"
				:aria-expanded="hamburgerOpen"
				@click="hamburgerOpen = !hamburgerOpen"
			/>
		</div>

		<div
			:id="menuId"
			class="grow flex flex-col sm:max-lg:flex-row gap-2"
			:aria-hidden="showHamburger && !hamburgerOpen"
		>
			<ul class="grow flex flex-col sm:max-lg:flex-row gap-2">
				<AppNavItem
					to="tracker.index"
					label="Home"
					:icon="faHouseCircleCheck"
					:legacy="true"
					:show-text="showHamburger"
					:tooltip-position
				/>
				<AppNavItem
					to="management.manage"
					label="Manager Dashboard"
					:icon="faBusinessTime"
					:show-text="showHamburger"
					:tooltip-position
				/>
				<AppNavItem
					to="attendee-logs.index"
					label="Attendee Logs"
					:icon="faBookOpen"
					:legacy="true"
					:show-text="showHamburger"
					:tooltip-position
				/>
				<AppNavItem
					to="admin.users"
					label="Users"
					:icon="faUsers"
					:legacy="true"
					:show-text="showHamburger"
					:tooltip-position
				/>
				<AppNavItem
					to="admin.events"
					label="Events"
					:icon="faCalendarDay"
					:legacy="true"
					:show-text="showHamburger"
					:tooltip-position
				/>
				<AppNavItem
					to="admin.reports"
					label="Reports"
					:icon="faFileLines"
					:legacy="true"
					:show-text="showHamburger"
					:tooltip-position
				/>
				<AppNavItem
					to="admin.site"
					label="Configuration"
					:icon="faScrewdriverWrench"
					:legacy="true"
					:show-text="showHamburger"
					:tooltip-position
				/>
			</ul>

			<ul class="flex flex-col sm:max-lg:flex-row gap-1">
				<AppNavItem
					label="About Tracker"
					:icon="faInfoCircle"
					:show-text="showHamburger"
					:tooltip-position
				/>
				<AppNavItem
					label="Preferences"
					:icon="faCog"
					:show-text="showHamburger"
					:tooltip-position
					@click="showSettingsModal = true"
				/>
				<AppNavLogoutItem :show-hamburger :tooltip-position />
			</ul>
		</div>

		<LocalSettingsModal v-model:visible="showSettingsModal" />
	</nav>
</template>

<script setup lang="ts">
import { ref, toRef, useId } from 'vue';
import { useBreakpoints } from '../lib/media-query';

import {
	faHouseCircleCheck,
	faBookOpen,
	faBusinessTime,
	faUsers,
	faCalendarDay,
	faFileLines,
	faScrewdriverWrench,
	faInfoCircle,
	faBars,
	faClose,
	faCog,
} from '@fortawesome/free-solid-svg-icons';
import AppNavItem from './AppNavItem.vue';
import AppNavLogoutItem from './AppNavLogoutItem.vue';
import LocalSettingsModal from './LocalSettingsModal.vue';

const { isNotSm, isNotLg } = useBreakpoints();

const showSettingsModal = ref(false);
const showHamburger = toRef(() => isNotSm.value);
const hamburgerOpen = ref(false);
const menuId = useId();

const tooltipPosition = toRef(() => (isNotLg.value ? 'bottom' : 'right'));
</script>
