<template>
	<IconButton
		:icon="faCircleQuestion"
		variant="text"
		severity="secondary"
		v-tooltip.bottom="tooltip ?? 'Help'"
		v-bind="$props"
		@click="dialogVisible = true"
	/>

	<Dialog modal class="w-full sm:w-lg mx-2" :header :dismissable-mask="true" v-model:visible="dialogVisible">
		<slot><p>{{ text }}</p></slot>
	</Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';

import { faCircleQuestion } from '@fortawesome/free-regular-svg-icons';
import IconButton from '@/Components/Common/IconButton.vue';

defineProps<{
	text?: string;
	header?: string;
	tooltip?: string;
}>();

const dialogVisible = ref(false);
</script>
