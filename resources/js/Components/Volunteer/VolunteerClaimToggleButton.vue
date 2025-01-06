<template>
	<ToggleButton
		v-model="checked"
		on-label="Claimed"
		off-label="Unclaimed"
		:disabled="disabled || request.processing.value"
	>
		<template #icon>
			<FontAwesomeIcon
				:icon="
					request.processing.value
						? faCircleNotch
						: claim
						? faCheck
						: faXmark
				"
				:spin="request.processing.value"
			/>
		</template>
	</ToggleButton>
</template>

<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { useRequest } from '@/lib/request';
import { useConfirm } from '@/lib/confirm';
import type Volunteer from '@/data/Volunteer';
import type Reward from '@/data/Reward';
import type RewardClaim from '@/data/RewardClaim';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCircleNotch, faCheck, faXmark } from '@fortawesome/free-solid-svg-icons';

const { reward, disabled = false } = defineProps<{
	reward: Reward;
	disabled?: boolean;
}>();
const volunteer = defineModel<Volunteer>();

const request = useRequest();
const { confirm } = useConfirm();

const claim = computed(() => volunteer.value!.reward_claims.find((claim) => claim.reward_id === reward.id));

const checked = ref(Boolean(claim.value));
let checkedResetting = false;

// This watcher is implemented in a hacky way since there isn't a way to interrupt ToggleButton clicks/changes.
// We have to immediately revert the change to the checked value when moving from checked to unchecked since we have
// a confirmation dialog for that action. We just verify that the checked status matches what we expect at every step,
// and if it doesn't, reset it.
watch(checked, async (makeClaim) => {
	// Ignore this change if it's from resetting
	if (checkedResetting) {
		checkedResetting = false;
		return;
	}

	// Reset the checked status immediately
	resetChecked();

	// Claim/unclaim the reward
	if (makeClaim) await claimReward();
	else await unclaimReward();

	// Reset the checked status once everything is done
	resetChecked();
});

watch(claim, () => resetChecked());

/**
 * Sends a request to claim the reward for the user and modifies the model appropriately
 */
async function claimReward() {
	// Make the request
	const { reward_claim: newClaim } = await request.put<{
		reward_claim: RewardClaim;
	}>(['users.claims.store', volunteer.value!.user.id], {
		reward_id: reward.id,
	});

	// Store the claim
	volunteer.value!.reward_claims.push(newClaim);
}

/**
 * Sends a request to unclaim the reward for the user and modifies the model appropriately
 */
async function unclaimReward() {
	// Confirm the change
	const confirmed = await confirm('Unclaim reward?', {
		accept: { label: 'Unclaim', severity: 'danger' },
	});
	if (!confirmed) return;

	// Make the request
	await request.del(['claims.destroy', claim.value!.id]);
	const claimIdx = volunteer.value!.reward_claims.findIndex((clm) => clm.id === claim.value!.id);

	// Remove the claim
	volunteer.value!.reward_claims.splice(claimIdx, 1);
}

/**
 * Resets the checked value to the expected claim status and ensures the watcher doesn't fire for this happening.
 */
function resetChecked() {
	nextTick(() => {
		const claimed = Boolean(claim.value);
		if (checked.value === claimed) return;
		checkedResetting = true;
		checked.value = claimed;
	});
}
</script>
