<template>
	<component
		:is="wrapper"
		:class="{
			'w-12 h-12 text-xl': !showText,
			'h-10 text-lg': showText,
		}"
		:aria-current="isActive && to ? 'page' : undefined"
	>
		<component
			:is="isButton ? 'button' : legacy ? LegacyLink : Link"
			class="flex w-full h-full p-2 gap-2 rounded-lg transition-colors items-center"
			:class="{
				'justify-center': !showText,
				[`${color} hover:bg-emphasis`]: !isActive,
				[`${activeColor} bg-primary hover:bg-primary-emphasis`]:
					isActive,
			}"
			:to
			:method
			:aria-label="!showText ? label : undefined"
			v-tooltip:[{position:tooltipPosition}]="
				tooltip && !showText ? label : undefined
			"
			@click="isButton && to && navigate()"
			v-bind="$attrs"
		>
			<slot>
				<slot name="icon">
					<FontAwesomeIcon :icon :class="{ 'w-10': showText }" />
				</slot>

				<slot name="text">
					<span v-if="showText">{{ label }}</span>
				</slot>
			</slot>
		</component>
	</component>
</template>

<script setup lang="ts">
import { computed, toRef } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Method } from '@inertiajs/core';
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core';
import type { RouteName } from 'vendor/tightenco/ziggy/src/js';
import { useRoute } from '../lib/route';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import Link from './Link.vue';
import LegacyLink from './LegacyLink.vue';

defineOptions({ inheritAttrs: false });
const {
	to,
	method = 'get',
	legacy = false,
	active = false,
	color = 'text-muted-color',
	activeColor = 'text-primary-contrast',
	showText = false,
	tooltip = true,
	tooltipPosition,
	wrapper = 'li',
} = defineProps<{
	icon: IconDefinition;
	label: string;
	to?: RouteName;
	method?: Method;
	legacy?: boolean;
	active?: boolean;
	color?: string;
	activeColor?: string;
	showText?: boolean;
	tooltip?: boolean;
	tooltipPosition?: string;
	wrapper?: string;
}>();

const route = useRoute();

const isActive = computed(() => active || Boolean(to && route().current()?.startsWith(to)));
const isButton = toRef(() => !to || method !== 'get');

/**
 * Navigates to the given route
 */
function navigate() {
	router.visit(route(to!), { method });
}
</script>
