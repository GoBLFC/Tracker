<template>
	<span class="cursor-help">
		<FontAwesomeIcon :icon="faCircleQuestion" v-tooltip="text" />
		<span class="tw-sr-only">{{ text }}</span>
	</span>
</template>

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleQuestion } from '@fortawesome/free-regular-svg-icons';

defineProps<{ text: string }>();
</script>
