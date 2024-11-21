<template>
	<li
		class="w-12 h-12 text-xl text-center"
		:aria-current="isActive ? 'page' : undefined"
	>
		<component
			:is="isButton ? 'button' : legacy ? LegacyLink : Link"
			:to
			class="block w-full h-full p-2 rounded-lg transition-colors"
			:class="{
				[`${color} bg-transparent hover:bg-emphasis`]: !isActive,
				[`${activeColor} bg-primary hover:bg-primary-emphasis`]:
					isActive,
			}"
			@click="isButton && to && navigate()"
			v-tooltip:[{position:tooltipPosition}]="label"
			v-bind="$attrs"
		>
			<slot>
				<FontAwesomeIcon :icon />
			</slot>
		</component>
	</li>
</template>

<script setup lang="ts">
import { computed, toRef } from 'vue';
import { router } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import type { Method } from '@inertiajs/core';
import type { IconDefinition } from '@fortawesome/free-solid-svg-icons';
import type { RouteName } from 'vendor/tightenco/ziggy/src/js';
import { useRoute } from '../lib/route';
import Link from './Link.vue';
import LegacyLink from './LegacyLink.vue';

defineOptions({ inheritAttrs: false });
const {
	to,
	method,
	legacy = false,
	color = 'text-muted-color',
	activeColor = 'text-primary-contrast',
	tooltipPosition,
} = defineProps<{
	icon: IconDefinition;
	label: string;
	to?: RouteName;
	method?: Method;
	legacy?: boolean;
	button?: boolean;
	color?: string;
	activeColor?: string;
	tooltipPosition?: string;
}>();

const route = useRoute();

const isActive = computed(() => Boolean(to && route().current()?.startsWith(to)));
const isButton = toRef(() => !to || method !== 'get');

/**
 * Navigates to the given route
 */
function navigate() {
	router.visit(route(to!), { method });
}
</script>
