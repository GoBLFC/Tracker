<template>
	<button
		type="button"
		class="btn btn-sm"
		:class="{ 'btn-danger': claim, 'btn-success': !claim }"
		:disabled="request.processing.value"
		@click="toggleClaim"
	>
		<template v-if="!request.processing.value">
			<FontAwesomeIcon class="me-1" :icon="claim ? faXmark : faCheck" />
			{{ claim ? "Unclaim" : "Claim" }}
		</template>
		<template v-else>
			<FontAwesomeIcon class="me-1" :icon="faCircleNotch" spin />
			{{ claim ? "Unclaiming" : "Claiming" }}&hellip;
		</template>
	</button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch, faCheck, faXmark } from '@fortawesome/free-solid-svg-icons';
import { useRequest } from '../lib/request';
import { useToast } from '../lib/toast';
import type Volunteer from '../data/Volunteer';
import type Reward from '../data/Reward';
import type RewardClaim from '../data/RewardClaim';

const { reward } = defineProps<{ reward: Reward }>();
const volunteer = defineModel<Volunteer>();

const request = useRequest();
const toast = useToast();

const claim = computed(() => volunteer.value!.claims.find((claim) => claim.reward_id === reward.id));

/**
 * Sends a request to claim or unclaim the reward for the user and modifies the model appropriately
 */
async function toggleClaim() {
	await (claim.value ? unclaimReward() : claimReward());
}

/**
 * Sends a request to claim the reward for the user and modifies the model appropriately
 */
async function claimReward() {
	const { reward_claim: newClaim } = await request.put<{
		reward_claim: RewardClaim;
	}>(['users.claims.store', volunteer.value!.user.id], {
		reward_id: reward.id,
	});
	volunteer.value!.claims.push(newClaim);
}

/**
 * Sends a request to unclaim the reward for the user and modifies the model appropriately
 */
async function unclaimReward() {
	const confirmed = await toast.confirm('Unclaim reward?', `${reward.hours}hr reward: ${reward.name}`, {
		icon: 'warning',
		showCancel: true,
		confirmText: 'Unclaim',
	});
	if (!confirmed) return;

	await request.del(['claims.destroy', claim.value!.id]);
	const claimIdx = volunteer.value!.claims.findIndex((clm) => clm.id === claim.value!.id);
	volunteer.value!.claims.splice(claimIdx, 1);
}
</script>
