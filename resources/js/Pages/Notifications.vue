<template>
	<div class="grow max-w-[64rem] mx-auto flex flex-col justify-center gap-6">
		<Head :title="`${notifications.length} alert${plural}`" />

		<template v-if="notifications.length > 0">
			<h1 class="text-3xl font-light">New alert{{ plural }}</h1>

			<div class="flex flex-col gap-4">
				<Message v-for="notif of notifications">
					<h2 class="text-xl mb-1">{{ notif.title }}</h2>
					<p v-html="description(notif)"></p>
				</Message>
			</div>

			<IconButton
				label="Acknowledge"
				severity="success"
				:icon="faCheck"
				class="w-fit"
				:loading="request.processing.value"
				@click="acknowledge()"
			/>
		</template>

		<template v-else>
			<h1 class="text-3xl font-light text-muted-color-emphasis">
				No alerts.
			</h1>
		</template>
	</div>
</template>

<script setup lang="ts">
import { toRef } from 'vue';
import { Head } from '@inertiajs/vue3';

import { useInertiaRequest } from '@/lib/request';
import type Notification from '@/data/Notification';

import { faCheck } from '@fortawesome/free-solid-svg-icons';
import IconButton from '@/Components/Common/IconButton.vue';

const { notifications } = defineProps<{ notifications: Notification[] }>();

const request = useInertiaRequest();

const plural = toRef(() => (notifications.length !== 1 ? 's' : ''));

/**
 * Gets the description of a notification and replaces all newlines with HTML <br /> tags
 * @param notif
 */
function description(notif: Notification) {
	return notif.description.replace(/\n/g, '<br />');
}

/**
 * Acknowledges all notifications, navigating to the main tracker page
 */
function acknowledge() {
	request.post('notifications.acknowledge');
}
</script>
