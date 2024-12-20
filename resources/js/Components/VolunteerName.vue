<template>
	<span v-tooltip.bottom="allNames">
		{{ displayName }}
		<span v-if="showId" class="font-normal">(#{{ user.badge_id }})</span>
	</span>
</template>

<script setup lang="ts">
import { toRef } from 'vue';
import type User from '../data/impl/User';
import type RawUser from '../data/User';
import type Volunteer from '../data/Volunteer';

const { volunteer, showId = false } = defineProps<{
	volunteer: Volunteer | RawUser | User;
	showId?: boolean;
}>();

const user = toRef(() => ('user' in volunteer ? volunteer.user : volunteer));
const displayName = toRef(() => user.value.badge_name ?? user.value.username);
const allNames = toRef(
	() =>
		`Badge Name:\n${user.value.badge_name ?? 'N/A'}\n\nUsername:\n${
			user.value.username
		}\n\nReal Name:\n${user.value.first_name} ${user.value.last_name}`,
);
</script>
