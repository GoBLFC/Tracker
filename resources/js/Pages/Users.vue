<template>
	<div class="grow flex flex-col gap-4">
		<FullContentHeightPanel header="Users" class="grow">
			<UsersTable
				:users
				:total-records="total"
				:rows="perPage"
				:first="first - 1"
				:sort-field="sortBy"
				:sort-order="sortDir === 'asc' ? 1 : -1"
				:filter-values="filters"
			/>
		</FullContentHeightPanel>
	</div>
</template>

<script setup lang="ts">
import type User from '@/data/User';

import UsersTable from '@/Components/User/UsersTable.vue';
import FullContentHeightPanel from '@/Components/Common/FullContentHeightPanel.vue';

defineProps<{
	users: User[];
	total: number;
	perPage: number;
	first: number;
	sortBy: string;
	sortDir: string;
	filters: {
		name: string | null;
		badge_id: number | null;
		role: number | null;
	};
}>();
</script>
