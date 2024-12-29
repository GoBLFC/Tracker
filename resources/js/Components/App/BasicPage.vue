<template>
	<div class="grow flex flex-col justify-center items-center gap-8">
		<Head :title />

		<FontAwesomeIcon class="text-6xl" :icon />

		<h1 class="text-3xl text-center font-light">{{ title }}</h1>

		<Message :severity>
			<slot />
		</Message>
	</div>
</template>

<script setup lang="ts">
import type { MessageProps } from 'primevue/message';
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const { severity = 'secondary' } = defineProps<{
	title: string;
	icon: IconDefinition;
	severity?: MessageProps['severity'];
}>();
</script>
