<template>
	<div class="grow flex flex-col gap-4 min-h-0">
		<Head :title="`Reports${event ? ` | ${event?.name}` : ''}`" />

		<div
			class="flex items-center gap-3"
			:class="{
				'justify-center': events.length === 0,
				'justify-between': events.length > 0,
			}"
		>
			<EventNavigator
				:event
				:events
				:resolver="eventRequestResolver"
				action-word="view"
				@changing="eventLoading = true"
				@finish="eventLoading = false"
			/>
		</div>

		<FullContentHeightPanel>
			<template #header>
				<Menubar :model="tabs">
					<template #item="{ item, props, hasSubmenu }">
						<Link :to="item.route" :only="item.only">
							{{ item.label }}
							<span v-if="hasSubmenu" class="pi pi-fw pi-angle-down" />
						</Link>
					</template>
				</Menubar>
			</template>

			{{ tabs }}
		</FullContentHeightPanel>

		<TabbedPanel :tabs :value="selectedReport?.slug" class="grow min-h-0">
			<template #default="{ loading: tabLoading }">
				<ReportTable
					v-if="selectedReport"
					:report
					:loading="report && (eventLoading || tabLoading)"
					:skeleton="!report && (eventLoading || tabLoading)"
					:skeleton-columns="selectedReport?.standardColumns"
					:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
				/>

				<p v-else-if="!selectedReport" class="h-full flex items-center justify-center text-xl text-muted-color">
					Select a report to view above.
				</p>
			</template>
		</TabbedPanel>
	</div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useRouteParams } from '@/lib/route';
import type Event from '@/data/Event';
import type { EventId } from '@/data/Event';

import { Head } from '@inertiajs/vue3';
import EventNavigator from '@/Components/Event/EventNavigator.vue';
import TabbedPanel from '@/Components/Common/TabbedPanel.vue';
import ReportTable, { type Report } from '@/Components/Report/ReportTable.vue';
import FullContentHeightPanel from '@/Components/Common/FullContentHeightPanel.vue';
import Link from '@/Components/Common/Link.vue';

const { event, report, reports } = defineProps<{
	report?: Report;
	reports: { name: string; slug: string; eventOnly: boolean; standardColumns: string[]; extraParam?: { key: string; default: string; choices?: [number: string] } }[];
	exportTypes?: Record<string, string>;
	event: Event | null;
	events: Event[];
}>();

const routeParams = useRouteParams();

const usableReports = computed(() => (event ? reports : reports.filter((report) => !report.eventOnly)));
const tabs = computed(() =>
	usableReports.value.map((report) => {
		const route = [`${event ? 'events.' : ''}reports.show`, event ? [event.id, report.slug] : [report.slug]];

		let items = undefined;
		if (report.extraParam?.choices) {
			items = [];
			for (const [value, label] of Object.entries(report.extraParam.choices)) {
				items.push({ label, route });
			}
		}

		return {
			label: report.name,
			route,
			only: ['report'],
			value: report.slug,
			items,
		};
	}),
);

const eventLoading = ref(!report);
const selectedReport = computed(() =>
	routeParams.value.report ? reports.find((report) => report.slug === routeParams.value.report) : null,
);

watch(
	() => report,
	() => (eventLoading.value = false),
	{ once: true },
);

/**
 * Resolves an event ID to a navigable URL and additional properties to pass to the router
 */
function eventRequestResolver(eventId: EventId) {
	return {
		url: route(
			`${eventId ? 'events.' : ''}reports.${selectedReport.value ? 'show' : 'index'}`,
			[eventId, selectedReport.value?.slug].filter((p) => Boolean(p)),
		),
		only: ['event', 'report'],
	};
}
</script>
