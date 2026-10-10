<template>
	<DataTable
		v-if="!skeleton"
		:value="report?.data?.body"
		paginator
		:rows="25"
		:rows-per-page-options="[10, 25, 50, 100]"
		sortable
		:sort-field="report?.meta?.defaultSort?.column ? String(report?.meta?.defaultSort?.column) : undefined"
		:sort-order="report?.meta?.defaultSort?.direction === 'desc' ? -1 : 1"
		v-model:filters="filters"
		:global-filter-fields="columns ? columns.map(c => c.field) : undefined"
		filter-display="menu"
		scrollable
		scroll-height="flex"
	>
		<Column
			v-for="col of columns"
			:key="col.field"
			:field="col.field"
			:data-type="col.dataType"
			:header="col.header"
			:footer="col.footer"
			sortable
		>
			<template v-if="col.numberFormat" #body="{ data }">
				{{ col.numberFormat.format(data[col.field]) }}
			</template>
			<template v-else-if="col.format.type === 'date'" #body="{ data }">
				{{ isoToPreferredTime(data[col.field]).toLocaleString(DateTime.DATETIME_SHORT) }}
			</template>
			<template v-else-if="col.format.type === 'string'" #body="{ data }">
				<ul v-if="/^(?:- (.+?): ((.|\n)+)\n?)+$/.test(data[col.field])" class="list-disc">
					<li v-for="item of data[col.field].matchAll(/^- (.+?): (.+)$/gm)" :key="item[0]">
						<strong>{{ item[1] }}:</strong>
						{{ item[2] }}
					</li>
				</ul>
				<template v-else>{{ data[col.field] }}</template>
			</template>

			<template #filter="{ filterModel }">
				<InputText v-model="filterModel.value" />
			</template>
		</Column>
	</DataTable>

	<SkeletonTable v-else :columns="skeletonColumns!" :rows="skeletonRows" />
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { DateTime } from 'luxon';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import type { DataTableOperatorFilterMetaData } from 'primevue/datatable';
import { useTime } from '@/lib/time';

import SkeletonTable from '@/Components/Common/SkeletonTable.vue';

const {
	report,
	skeleton = false,
	skeletonRows = 10,
} = defineProps<
	{
		report?: Report | null;
	} & (
		| { skeleton: false; skeletonRows?: number; skeletonColumns?: string[] }
		| { skeleton: true; skeletonRows?: number; skeletonColumns: string[] }
	)
>();

const { isoToPreferredTime } = useTime();

const columns = computed(() => {
	if (!report?.data) return null;

	const columns = [];
	for (let i = 0; i < report.data.head.length; i++) {
		// Build a number format to use (if applicable)
		const format = report.meta.formats[i];
		const numberFormat =
			format.type === 'number'
				? new Intl.NumberFormat(undefined, {
						maximumFractionDigits: format.decimal ? 2 : 0,
						minimumFractionDigits: format.decimal ? 2 : 0,
						useGrouping: format.separators ? 'auto' : false,
					})
				: null;

		// Format the total for the column (if applicable)
		const total = report.data.totals?.[i];
		const footer = total
			? typeof total === 'number' && numberFormat
				? numberFormat.format(total)
				: String(total)
			: undefined;

		columns.push({
			field: String(i),
			dataType: format.type === 'number' ? 'numeric' : format.type === 'string' ? 'text' : format.type,
			header: report.data.head[i],
			footer,
			format,
			numberFormat,
		});
	}

	return columns;
});

const filters = ref<Record<string, DataTableOperatorFilterMetaData>>({});

watch(columns, (columns) => {
	if (!columns) return;

	// Remove all existing filters
	for (const key of Object.keys(filters.value)) {
		delete filters.value[key];
	}

	// Add a new filter for each column
	for (const col of columns) {
		filters.value[col.field] = {
			operator: FilterOperator.AND,
			constraints: [
				{
					value: null,
					matchMode:
						col.dataType === 'numeric'
							? FilterMatchMode.EQUALS
							: col.dataType === 'date'
								? FilterMatchMode.DATE_IS
								: FilterMatchMode.CONTAINS,
				},
			],
		};
	}
});

/**
 * Comprehensive data to display a report
 */
export type Report = {
	data: { body: (string | number)[][]; head: string[]; totals?: (string | number)[] };
	meta: {
		formats: ({ type: 'string' | 'date' } | { type: 'number'; decimal: boolean; separators: boolean })[];
		defaultSort: { column: number; direction: 'asc' | 'desc' };
	};
};
</script>
