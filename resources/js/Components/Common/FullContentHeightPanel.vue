<template>
	<Panel
		class="flex flex-col"
		:pt="{
			contentContainer: { class: 'grow' },
			content: { class: 'h-full' },
		}"
	>
		<slot />
	</Panel>
</template>
