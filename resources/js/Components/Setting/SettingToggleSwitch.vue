<template>
	<ToggleSwitch
		v-model="form.value"
		:disabled="form.processing"
		@update:model-value="form.patch(route('settings.update', setting))"
	>
		<template #handle v-if="form.processing">
			<FontAwesomeIcon :icon="faCircleNotch" class="text-primary" spin />
		</template>
	</ToggleSwitch>
</template>

<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

import { useRoute } from '@/lib/route';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch } from '@fortawesome/free-solid-svg-icons';

const { setting, value } = defineProps<{ setting: string; value: boolean }>();

const form = useForm({ value });
const route = useRoute();

watch(
	() => value,
	() => {
		if (form.processing) form.cancel();
		form.value = value;
	},
);
</script>
