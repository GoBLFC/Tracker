<template>
	<Button
		variant="link"
		size="small"
		class="mt-[-0.375rem] relative top-[0.1875rem]"
		aria-label="View Volunteer"
		v-tooltip.left="'View Volunteer'"
	>
		<template #icon>
			<FontAwesomeIcon :icon="faMagnifyingGlass" />
		</template>
	</Button>
</template>

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faMagnifyingGlass } from '@fortawesome/free-solid-svg-icons';
</script>
