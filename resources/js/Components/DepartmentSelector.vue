<template>
	<FloatLabel variant="on" class="grow w-64">
		<Select
			v-model="department"
			:options="departments"
			data-key="id"
			:label-id="selectId"
			fluid
			v-bind="$attrs"
		>
			<template
				#value="{
					value: dept,
				}: {
					value: Department | null | undefined,
				}"
			>
				<span
					v-if="dept"
					class="flex grow gap-2 justify-between items-center"
				>
					<span class="truncate">{{ dept.name }}</span>
					<Tag
						v-if="dept.hidden"
						value="Hidden"
						severity="secondary"
						class="text-xs"
					/>
				</span>

				<span v-else>&nbsp;</span>
			</template>

			<template #option="{ option: dept }: { option: Department }">
				<div class="flex grow gap-6 justify-between items-center">
					<span class="truncate">{{ dept.name }}</span>
					<Tag
						v-if="dept.hidden"
						value="Hidden"
						severity="secondary"
						class="text-xs"
					/>
				</div>
			</template>
		</Select>

		<label :for="selectId">Department</label>
	</FloatLabel>
</template>

<script setup lang="ts">
import { useId } from 'vue';
import type Department from '../data/Department';

defineOptions({ inheritAttrs: false });
defineProps<{ departments: Department[] }>();
const department = defineModel<Department | null | undefined>();

const selectId = useId();
</script>
