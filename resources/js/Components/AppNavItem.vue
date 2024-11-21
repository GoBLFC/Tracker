<template>
	<li
		class="w-12 h-12 text-xl text-center"
		:aria-current="isActive ? 'page' : undefined"
	>
		<component
			:is="legacy ? LegacyLink : Link"
			:to
			class="block h-full p-2 rounded-lg transition-colors"
			:class="{
				[`${color} bg-transparent hover:bg-emphasis`]: !isActive,
				[`${activeColor} bg-primary hover:bg-primary-emphasis`]:
					isActive,
			}"
			v-tooltip="label"
			v-bind="$attrs"
		>
			<slot>
				<FontAwesomeIcon :icon />
			</slot>
		</component>
	</li>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import type { IconDefinition } from '@fortawesome/free-solid-svg-icons';
import type { RouteName } from 'vendor/tightenco/ziggy/src/js';
import { useRoute } from '../lib/route';
import Link from './Link.vue';
import LegacyLink from './LegacyLink.vue';

defineOptions({ inheritAttrs: false });
const {
	to,
	legacy = false,
	color = 'text-muted-color',
	activeColor = 'text-primary-contrast',
} = defineProps<{
	icon: IconDefinition;
	label: string;
	to?: RouteName;
	legacy?: boolean;
	color?: string;
	activeColor?: string;
}>();

const route = useRoute();

const isActive = computed(() => Boolean(to && route().current()?.startsWith(to)));
</script>
