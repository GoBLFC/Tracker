<template>
	<div class="flex flex-col h-full gap-4">
		<Head title="Manager Dashboard" />

		<!-- Event selector -->
		<div
			class="flex gap-3 items-center"
			:class="{
				'justify-center': events.length === 0,
				'justify-between': events.length > 0,
			}"
		>
			<EventSelector
				:event
				:events
				:resolver="eventRequestResolver"
				action-word="manage"
			/>

			<div
				v-if="isReadOnly"
				class="text-2xl text-muted-color"
				v-tooltip.left="'Read-only'"
			>
				<FontAwesomeIcon :icon="faEye" aria-hidden="true" />
				<span class="sr-only">Read-only</span>
			</div>
		</div>

		<div class="flex flex-col xl:flex-row xl:flex-wrap h-full gap-4">
			<!-- Recent activity -->
			<Panel
				header="Recent Activity"
				class="grow"
				:pt="{
					contentContainer: { class: 'h-full' },
					content: { class: 'h-full' },
				}"
			>
				<template v-if="recentTimeActivities.length > 0">
					<TimeActivitiesTable
						class="w-full"
						:activities="recentTimeActivities"
						:now
						@select="loadVolunteer"
					/>
				</template>

				<p v-else>There is no recent shift activity.</p>
			</Panel>

			<!-- Ongoing shifts -->
			<Panel
				header="Ongoing Shifts"
				class="grow"
				:pt="{
					contentContainer: { class: 'h-full' },
					content: { class: 'h-full' },
				}"
			>
				<template v-if="longestOngoingEntries.length > 0">
					<TimeEntriesTable
						class="w-full"
						:entries="longestOngoingEntries"
						:now
						@select="loadVolunteer"
					/>
				</template>

				<p v-else>There are no ongoing shifts.</p>
			</Panel>

			<!-- Volunteer search -->
			<Panel header="Volunteer Search" class="grow">
				<UserSearchCard class="mb-4" @select="loadVolunteer" />

				<VolunteerManageCard
					v-if="volunteer"
					ref="volunteer-card"
					v-model="volunteer"
					:event
					:rewards
					:departments
					:now
					@close="volunteer = null"
				/>

				<UserCreateCard header-tag="div" class="mb-4" />
			</Panel>

			<!-- Quick settings -->
			<Panel header="Quick Settings" class="grow">
				<dl>
					<div class="flex items-center gap-4">
						<dt>
							<KioskToggleSwitch
								:aria-labelledby="kioskSettingId"
							/>
						</dt>
						<dd>
							<p
								class="text-lg text-semibold"
								:id="kioskSettingId"
							>
								Kiosk authorization
							</p>
							<p>
								Authorizing this device as a kiosk will allow
								non-staff volunteers to check in or out on this
								device. This is required when setting up
								dedicated devices pre-con for checking in or
								out. Kiosks remain authorized for
								{{ kioskLifetimeText }}.
							</p>
						</dd>
					</div>
				</dl>
			</Panel>
		</div>
	</div>
</template>

<script setup lang="ts">
import {
	ref,
	computed,
	watch,
	useId,
	useTemplateRef,
	nextTick,
	onMounted,
	onUnmounted,
	toRef,
} from "vue";
import { router, Head } from "@inertiajs/vue3";
import humanizeDuration from "humanize-duration";
import { useUser } from "../lib/user";
import { useSettings } from "../lib/settings";
import { useNow } from "../lib/time";
import { useRequest } from "../lib/request";
import { useRoute } from "../lib/route";
import type Volunteer from "../data/Volunteer";
import type Event from "../data/Event";
import type { EventId } from "../data/Event";
import type Department from "../data/Department";
import type Reward from "../data/Reward";
import type TimeEntryActivity from "../data/TimeEntryActivity";
import type TimeEntry from "../data/TimeEntry";
import type RewardClaim from "../data/RewardClaim";
import type { UserId } from "../data/User";
import EventSelector from "../Components/EventSelector.vue";
import UserSearchCard from "../Components/UserSearchCard.vue";
import VolunteerManageCard from "../Components/VolunteerManageCard.vue";
import TimeActivitiesTable from "../Components/TimeActivitiesTable.vue";
import TimeEntriesTable from "../Components/TimeEntriesTable.vue";
import UserCreateCard from "../Components/UserCreateCard.vue";
import KioskToggleSwitch from "../Components/KioskToggleSwitch.vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faEye } from "@fortawesome/free-solid-svg-icons";

const { event, kioskLifetime } = defineProps<{
	event: Event;
	events: Event[];
	departments: Department[];
	rewards: Reward[];
	kioskLifetime: number;
	recentTimeActivities: TimeEntryActivity[];
	longestOngoingEntries: TimeEntry[];
}>();

const route = useRoute();
const { activeEvent } = useSettings();
const { isAdmin } = useUser();
const { now } = useNow();

const isActiveEventSelected = toRef(
	() => event && event.id !== activeEvent.value?.id
);
const isReadOnly = toRef(() => !isAdmin && isActiveEventSelected.value);
const kioskSettingId = useId();
const kioskLifetimeText = computed(() =>
	humanizeDuration(kioskLifetime * 1000 * 60)
);

/**
 * Resolves an event ID to a navigable URL and additional properties to pass to the router
 */
function eventRequestResolver(eventId: EventId) {
	return {
		url: route("management.manage", eventId),
		only: ["longestOngoingEntries", "recentTimeActivities"],
	};
}

//
// Volunteer loading
//
const statsRequest = useRequest();
const claimsRequest = useRequest();
const volunteerCard = useTemplateRef("volunteer-card");

const volunteer = ref<Volunteer | null>(null);

watch(() => event, resetVolunteer);

/**
 * Loads all data for a volunteer user to provide to the user management card
 */
async function loadVolunteer(userId: UserId, attention = true) {
	const [newVolunteer, { reward_claims: claims }] = await Promise.all([
		statsRequest.get<Volunteer>([
			"tracker.user.stats.event",
			[userId, event.id],
		]),
		claimsRequest.get<{ reward_claims: RewardClaim[] }>([
			"users.claims.event",
			[userId, event.id],
		]),
	]);

	volunteer.value = {
		user: newVolunteer.user,
		stats: newVolunteer.stats,
		claims: claims,
	};

	if (attention) {
		nextTick(() => {
			volunteerCard.value!.attention();
		});
	}
}

/**
 * Clears the volunteer information, thus closing the volunteer card
 */
function resetVolunteer() {
	volunteer.value = null;
}

//
// Auto-refreshing
//
let refreshInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
	refreshInterval = setInterval(() => {
		router.reload({
			only: ["recentTimeActivities", "longestOngoingEntries"],
		});

		const shouldRefreshVolunteer =
			volunteer.value &&
			!statsRequest.processing.value &&
			!claimsRequest.processing.value;
		if (shouldRefreshVolunteer)
			loadVolunteer(volunteer.value!.user.id, false);
	}, 15000);
});

onUnmounted(() => {
	clearInterval(refreshInterval!);
	refreshInterval = null;
});
</script>
