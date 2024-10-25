<template>
	<div class="d-flex gap-1 justify-content-center flex-wrap">
		<div class="badge rounded-pill text-bg-warning">
			<FontAwesomeIcon :icon="faCode" class="me-1" />
			Dev Mode Enabled
		</div>

		<template v-if="user.isLoggedIn">
			<div class="badge rounded-pill text-bg-info">
				<FontAwesomeIcon :icon="faUser" class="me-1" />
				Your Badge ID: {{ user.badgeId }}
			</div>
			<div class="badge rounded-pill text-bg-info">
				<FontAwesomeIcon :icon="faUser" class="me-1" />
				Your UUID: {{ user.id }}
			</div>
			<div class="badge rounded-pill text-bg-primary">
				<FontAwesomeIcon :icon="faIdCard" class="me-1" />
				Role: {{ user.roleName }}
			</div>
		</template>

		<div
			class="badge rounded-pill"
			:class="{
				'text-bg-success': isKiosk,
				'text-bg-danger': !isKiosk,
			}"
		>
			<FontAwesomeIcon
				:icon="isKiosk ? faLockOpen : faLock"
				class="me-1"
			/>
			Kiosk: {{ isKiosk ? "Authorized" : "Unauthorized" }}
		</div>

		<div
			class="badge rounded-pill"
			:class="{
				'text-bg-success': activeEvent,
				'text-bg-danger': !activeEvent,
			}"
		>
			<FontAwesomeIcon :icon="faCalendarDay" class="me-1" />
			Event: {{ activeEvent?.name ?? "None active" }}
		</div>
	</div>
</template>

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCode, faUser, faIdCard, faLock, faLockOpen, faCalendarDay } from '@fortawesome/free-solid-svg-icons';
import { useUser } from '../lib/user';
import { useSettings } from '../lib/settings';

const user = useUser();
const { activeEvent, isKiosk } = useSettings();
</script>
