<template>
	<div class="card">
		<div class="card-header">Reward Claims</div>

		<div v-if="rewards.length > 0" class="card-body p-0">
			<div class="table-responsive">
				<table
					class="table table-dark table-borderless text-center mb-0"
				>
					<thead>
						<tr>
							<th
								v-for="reward of rewards"
								:key="reward.id"
								scope="col"
								class="pt-3"
							>
								{{ reward.hours }}hr: {{ reward.name }}
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td
								v-for="reward of rewards"
								:key="reward.id"
								class="pb-3"
							>
								<VolunteerClaimToggleButton
									v-model="volunteer"
									:reward="reward"
								/>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<p v-else class="card-body mb-0">There are no rewards available.</p>
	</div>
</template>

<script setup>
import VolunteerClaimToggleButton from "./VolunteerClaimToggleButton.vue";

defineProps({
	rewards: { type: Array, required: true },
});
const volunteer = defineModel();
</script>
