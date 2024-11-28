<template>
	<ResponsiveTag
		:label="`Checked ${checkedIn ? 'In' : 'Out'}${
			department ? `: ${department.name}` : ''
		}`"
		:icon="checkedIn ? faArrowRightToBracket : faArrowRightFromBracket"
		:severity="checkedIn ? 'success' : 'secondary'"
		class="md:justify-start gap-2 md:min-w-[8.8em]"
	/>
</template>

<script setup lang="ts">
import type Department from '../data/Department';

import { faArrowRightFromBracket, faArrowRightToBracket } from '@fortawesome/free-solid-svg-icons';
import ResponsiveTag from './ResponsiveTag.vue';

defineProps<{
	checkedIn: boolean;
	department?: Department | null;
}>();
</script>
