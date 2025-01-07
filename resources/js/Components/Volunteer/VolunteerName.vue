<template>
	<component
		:is="event ? Link : 'span'"
		:to="route"
		v-tooltip.bottom="allNames"
	>
		{{ displayName }}
		<span v-if="showId" class="font-normal">(#{{ user.badge_id }})</span>
	</component>
</template>

<script setup lang="ts">
import { toRef } from 'vue';
import type User from '@/data/impl/User';
import type RawUser from '@/data/User';
import type Volunteer from '@/data/Volunteer';
import type Event from '@/data/Event';
import type { EventId } from '@/data/Event';

import Link from '../Common/Link.vue';

const {
	volunteer,
	showId = false,
	event,
} = defineProps<{
	volunteer: Volunteer | RawUser | User;
	event?: Event | EventId | null;
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

const route = toRef(() =>
	event
		? [
				'management.manage.volunteer',
				[
					// @ts-expect-error: Can't use the "in" operator here for some reason, it breaks things a lot
					typeof event.id === 'string' ? event.id : event,
					user.value.id,
				],
			]
		: undefined,
);
</script>
