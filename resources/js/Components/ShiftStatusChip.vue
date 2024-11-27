<template>
	<Chip
		class="md:min-w-[8.7em] py-1"
		v-tooltip.bottom="
			`Checked ${checkedIn ? 'In' : 'Out'}${
				department ? `: ${department.name}` : ''
			}`
		"
	>
		<FontAwesomeIcon
			:icon="checkedIn ? faArrowRightToBracket : faArrowRightFromBracket"
			:class="{
				'text-green-500': checkedIn,
				'text-yellow-500': !checkedIn,
			}"
		/>
		<span class="sr-only md:not-sr-only">
			Checked {{ checkedIn ? "In" : "Out"
			}}{{ department ? `: ${department.name}` : "" }}
		</span>
	</Chip>
</template>

<script setup lang="ts">
import type Department from '../data/Department';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faArrowRightFromBracket, faArrowRightToBracket } from '@fortawesome/free-solid-svg-icons';

defineProps<{
	checkedIn: boolean;
	department?: Department | null;
}>();
</script>
