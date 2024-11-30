<template>
	<div class="flex flex-wrap gap-2 justify-center text-xs">
		<Chip label="Dev Mode Enabled" class="bg-amber-400 text-gray-950">
			<template #icon>
				<FontAwesomeIcon :icon="faCode" />
			</template>
		</Chip>

		<template v-if="isLoggedIn">
			<Chip
				:label="`User: ${badgeId} &nbsp;/&nbsp; ${uuid}`"
				class="bg-cyan-700 text-gray-50"
			>
				<template #icon>
					<FontAwesomeIcon :icon="faUser" />
				</template>
			</Chip>
			<Chip :label="`Role: ${roleName}`" class="bg-blue-700 text-gray-50">
				<template #icon>
					<FontAwesomeIcon :icon="faIdCard" />
				</template>
			</Chip>
		</template>

		<Chip
			:label="`Kiosk: ${isKiosk ? 'Authorized' : 'Unauthorized'}`"
			class="text-gray-50"
			:class="{
				'bg-green-700': isKiosk,
				'bg-red-700': !isKiosk,
			}"
		>
			<template #icon>
				<FontAwesomeIcon :icon="isKiosk ? faLockOpen : faLock" />
			</template>
		</Chip>

		<Chip
			:label="`Event: ${activeEvent?.name ?? 'None active'}`"
			class="text-gray-50"
			:class="{
				'bg-green-700': activeEvent,
				'bg-red-700': !activeEvent,
			}"
		>
			<template #icon>
				<FontAwesomeIcon :icon="faCalendarDay" />
			</template>
		</Chip>
	</div>
</template>

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCode, faUser, faIdCard, faLock, faLockOpen, faCalendarDay } from '@fortawesome/free-solid-svg-icons';
import { useUser } from '../lib/user';
import { useAppSettings } from '../lib/settings';

const { isLoggedIn, badgeId, id: uuid, roleName } = useUser();
const { activeEvent, isKiosk } = useAppSettings();
</script>
