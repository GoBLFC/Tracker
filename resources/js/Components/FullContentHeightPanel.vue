<template>
	<Panel
		:pt="{
			contentContainer: { class: 'h-full' },
			content: { class: 'h-full' },
		}"
	>
		<slot />
	</Panel>
</template>
