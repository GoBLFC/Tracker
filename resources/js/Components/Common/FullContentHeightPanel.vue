<template>
	<Panel
		class="flex flex-col"
		:pt="{
			contentContainer: { class: 'grow' },
			content: { class: 'h-full' },
		}"
	>
		<template #icons v-if="$slots.icons"><slot name="icons" /></template>

		<slot />
	</Panel>
</template>
