<template>
	<DataTable
		:value="new Array(rows)"
		paginator
		:rows
		:rows-per-page-options="[5, 10, 15, 20]"
		scrollable
		scroll-height="flex"
		:dt="{ paginator: { bottom: { border: { width: 0 } } } }"
	>
		<Column v-for="col of columns" :header="col">
			<template #body>
				<Skeleton />
			</template>
		</Column>
	</DataTable>
</template>

<script setup lang="ts">
const { rows = 10 } = defineProps<{
	columns: string[];
	rows?: number;
}>();
</script>
