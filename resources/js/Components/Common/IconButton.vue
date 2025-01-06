<template>
	<Button>
		<template #icon>
			<FontAwesomeIcon :icon />
		</template>

		<template #loadingicon>
			<FontAwesomeIcon :icon="faCircleNotch" spin />
		</template>
	</Button>
</template>

<script setup lang="ts">
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch } from '@fortawesome/free-solid-svg-icons';

defineProps<{ icon: IconDefinition }>();
</script>
