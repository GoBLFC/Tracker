<template>
	<BreadcrumbsPage
		:title="attendeeLog.name"
		:trail="[
			{ label: 'Attendee Logs', route: 'attendee-logs.index' },
			{
				label: event.name,
				route: ['events.attendee-logs.index', event.id],
			},
			{
				label: attendeeLog.name,
				route: ['attendee-logs.show', attendeeLog.id],
			},
		]"
	>
		<template #icons v-if="readOnly">
			<div class="text-2xl text-muted-color" v-tooltip.left="'Read-only'">
				<FontAwesomeIcon :icon="faEye" />
				<span class="tw-sr-only">Read-only</span>
			</div>
		</template>

		<div
			v-if="!readOnly"
			class="flex flex-col lg:flex-row lg:flex-wrap gap-4"
		>
			<AttendeeCreatePanel
				:attendee-log
				:read-only
				class="grow basis-1"
			/>

			<AttendeeCreatePanel
				v-if="isManager"
				:attendee-log
				:read-only
				gatekeeper
				class="grow basis-1"
			/>
		</div>

		<div class="grow flex flex-col xl:flex-row xl:flex-wrap gap-4">
			<FullContentHeightPanel
				header="Attendees"
				class="flex-auto min-w-[30%]"
			>
				<AttendeesTable
					:attendees="attendees"
					:attendee-log
					:rows="readOnly ? 15 : 10"
					:read-only
				/>
			</FullContentHeightPanel>

			<FullContentHeightPanel
				v-if="isManager"
				header="Gatekeepers"
				class="flex-auto min-w-[30%]"
			>
				<AttendeesTable
					:attendees="gatekeepers"
					:attendee-log
					:read-only
					:rows="readOnly ? 15 : 10"
					gatekeeper
				>
					<template #empty>
						<p>There aren't any gatekeepers yet.</p>
					</template>
				</AttendeesTable>
			</FullContentHeightPanel>
		</div>
	</BreadcrumbsPage>
</template>

<script setup lang="ts">
import { computed, toRef } from 'vue';
import { useUser } from '@/lib/user';
import { useReadOnly } from '@/lib/readonly';
import type AttendeeLog from '@/data/AttendeeLog';
import type Event from '@/data/Event';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEye } from '@fortawesome/free-solid-svg-icons';
import AttendeesTable from '@/Components/AttendeeLog/AttendeesTable.vue';
import AttendeeCreatePanel from '@/Components/AttendeeLog/AttendeeCreatePanel.vue';
import FullContentHeightPanel from '@/Components/Common/FullContentHeightPanel.vue';
import BreadcrumbsPage from '@/Components/App/BreadcrumbsPage.vue';

const { attendeeLog, event } = defineProps<{
	attendeeLog: AttendeeLog;
	event: Event;
	exportTypes?: Record<string, string>;
}>();

const { isManager } = useUser();
const isEventReadOnly = useReadOnly();

const attendees = computed(() => attendeeLog.users!.filter((u) => u.pivot.type === 'attendee'));
const gatekeepers = computed(() => attendeeLog.users!.filter((u) => u.pivot.type === 'gatekeeper'));
const readOnly = toRef(() => isEventReadOnly(event));
</script>
