<template>
	<nav
		class="shrink-0 flex flex-col sm:max-lg:flex-row mx-4 my-4 px-3 py-2 lg:px-2 rounded-lg gap-4 sm:gap-6 lg:gap-8 bg-surface-50 dark:bg-surface-900 overflow-clip lg:overflow-auto select-none -z-20"
		:style="{
			transition: 'max-height ease-in-out 400ms',
			'max-height': showHamburger
				? hamburgerOpen
					? expandedMenuHeight
					: '4rem'
				: undefined,
		}"
	>
		<div class="flex justify-between">
			<img
				src="@/../img/event-logo.png"
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
					v-for="item of mainMenuItems"
					:show-text="showHamburger"
					:tooltip-position
					v-bind="item"
				/>
			</ul>

			<ul class="flex flex-col sm:max-lg:flex-row gap-1">
				<AppNavItem
					to="about"
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

				<AppNavLogoutItem
					v-if="isLoggedIn"
					:show-hamburger
					:tooltip-position
				/>
				<AppNavItem
					v-else
					to="auth.login"
					label="Sign In"
					:icon="faArrowRightToBracket"
					:show-text="showHamburger"
					:tooltip-position
				/>
			</ul>
		</div>

		<LocalSettingsModal v-model:visible="showSettingsModal" />
	</nav>
</template>

<script setup lang="ts">
import { computed, ref, toRef, useId } from 'vue';
import { useBreakpoints } from '@/lib/media-query';
import { useUser } from '@/lib/user';

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
	faArrowRightToBracket,
} from '@fortawesome/free-solid-svg-icons';
import AppNavItem from './AppNavItem.vue';
import AppNavLogoutItem from './AppNavLogoutItem.vue';
import LocalSettingsModal from '../Dialogs/LocalSettingsModal.vue';

const { isNotSm, isNotLg } = useBreakpoints();
const { isManager, isAdmin, isGatekeeper, isLoggedIn } = useUser();

const hamburgerOpen = ref(false);
const showSettingsModal = ref(false);
const menuId = useId();

const tooltipPosition = toRef(() => (isNotLg.value ? 'bottom' : 'right'));
const showHamburger = toRef(() => isNotSm.value);
const expandedMenuHeight = toRef(() => `${(mainMenuItems.value.length + 3) * 3 + 4}rem`);

const mainMenuItems = computed(() => {
	const items = [
		{
			to: 'tracker.index',
			label: 'Home',
			icon: faHouseCircleCheck,
			legacy: false,
		},
	];

	if (isManager.value) {
		items.push({
			to: 'management.manage',
			label: 'Manager Dashboard',
			icon: faBusinessTime,
			legacy: false,
		});
	}

	if (isGatekeeper.value) {
		items.push({
			to: 'attendee-logs.index',
			label: 'Attendee Logs',
			icon: faBookOpen,
			legacy: false,
		});
	}

	if (isAdmin.value) {
		items.push(
			{
				to: 'users.index',
				label: 'Users',
				icon: faUsers,
				legacy: false,
			},
			{
				to: 'admin.events',
				label: 'Events',
				icon: faCalendarDay,
				legacy: true,
			},
			{
				to: 'admin.reports',
				label: 'Reports',
				icon: faFileLines,
				legacy: true,
			},
			{
				to: 'settings.index',
				label: 'Configuration',
				icon: faScrewdriverWrench,
				legacy: false,
			},
		);
	}

	return items;
});
</script>
