<template>
	<FullContentHeightPanel :pt="{ header: { class: 'p-0 block' } }">
		<template #header>
			<Tabs :value="route().current() ?? ''" class="grow">
				<TabList class="bg-transparent">
					<Tab
						v-for="tab of tabs"
						:key="tab.label"
						:value="Array.isArray(tab.route) ? tab.route[0] : tab.route"
						:as="Link"
						:to="tab.route"
						@click.prevent="nav(tab)"
					>
						<span class="flex items-center gap-2">
							<FontAwesomeIcon :icon="tab.icon" />
							{{ tab.label }}
						</span>
					</Tab>
				</TabList>
			</Tabs>
		</template>

		<slot />
	</FullContentHeightPanel>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useRoute } from '@/lib/route';
import type { IconDefinition } from '@fortawesome/free-solid-svg-icons';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import FullContentHeightPanel from '@/Components/Common/FullContentHeightPanel.vue';
import Link from '@/Components/Common/Link.vue';

defineProps<{ tabs: Tab[] }>();

const route = useRoute();

export interface Tab {
	label: string;
	icon: IconDefinition;
	route: Parameters<typeof route>;
	only?: string[];
}

/**
 * Navigates to a tab
 */
function nav(tab: Tab) {
	router.get(Array.isArray(tab.route) ? route(...tab.route) : route(tab.route), undefined, { only: tab.only });
}
</script>
