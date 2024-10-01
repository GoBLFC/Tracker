<template>
	<div class="d-flex gap-1 justify-content-center flex-wrap">
		<div class="badge rounded-pill text-bg-warning">Dev Mode Enabled</div>

		<div v-if="user.isLoggedIn" class="badge rounded-pill text-bg-info">
			Your Badge ID: {{ user.badgeId }}
		</div>
		<div v-if="user.isLoggedIn" class="badge rounded-pill text-bg-info">
			Your UUID: {{ user.id }}
		</div>
		<div v-if="user.isLoggedIn" class="badge rounded-pill text-bg-primary">
			Role: {{ user.roleName }}
		</div>

		<div
			class="badge rounded-pill"
			:class="{
				'text-bg-success': isKiosk,
				'text-bg-danger': !isKiosk,
			}"
		>
			Kiosk: {{ isKiosk ? "Authorized" : "Unauthorized" }}
		</div>

		<div
			class="badge rounded-pill"
			:class="{
				'text-bg-success': activeEvent,
				'text-bg-danger': !activeEvent,
			}"
		>
			Event: {{ activeEvent?.name ?? "None active" }}
		</div>
	</div>
</template>

<script setup>
import { useUser } from '../lib/user';
import { useSettings } from '../lib/settings';

const user = useUser();
const { activeEvent, isKiosk } = useSettings();
</script>
