<template>
	<div
		class="btn-group float-end"
		role="group"
		aria-label="Time entry actions"
	>
		<button
			v-if="!entry.stop"
			type="button"
			class="btn btn-sm btn-warning checkout"
			title="Checkout"
			:disabled="deleted || request.processing.value"
			@click="checkout"
		>
			<FontAwesomeIcon
				:icon="
					request.processing.value
						? faCircleNotch
						: faArrowRightFromBracket
				"
				:spin="request.processing.value"
			/>
		</button>
		<button
			type="button"
			class="btn btn-sm btn-danger delete"
			title="Delete"
			:disabled="deleted || request.processing.value"
			@click="del"
		>
			<FontAwesomeIcon
				:icon="request.processing.value ? faCircleNotch : faTrash"
				:spin="request.processing.value"
			/>
		</button>
	</div>
</template>

<script setup>
import { ref } from "vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
	faCircleNotch,
	faTrash,
	faArrowRightFromBracket,
} from "@fortawesome/free-solid-svg-icons";
import { useTime } from "../lib/time";
import { useToast } from "../lib/toast";
import { useRequest } from "../lib/request";

const { entry } = defineProps({
	entry: { type: Object, required: true },
});
const emit = defineEmits({ checkout: [Object], delete: [] });

const { isoToDateTimeString } = useTime();
const toast = useToast();
const request = useRequest();

const deleted = ref(false);

/**
 * Sends a request to end the time entry and emits the checkout event with the updated data if successful
 */
async function checkout() {
	const confirmed = await toast.confirm(
		"End time entry?",
		`Started at ${isoToDateTimeString(entry.start)}`,
		{
			icon: "warning",
			showCancel: true,
			confirmText: "Checkout",
		}
	);
	if (!confirmed) return;

	try {
		const data = await request.post([
			"tracker.time.checkout.post",
			entry.id,
		]);
		emit("checkout", data.time_entry);
	} catch {
		toast.error(
			"Unable to end time entry",
			"See the browser console for more information."
		);
	}
}

/**
 * Sends a request to delete the time entry and emits the delete event if successful
 */
async function del() {
	const confirmed = await toast.confirm(
		"Delete time entry?",
		`Started at ${isoToDateTimeString(entry.start)}`,
		{
			icon: "warning",
			showCancel: true,
			confirmText: "Delete",
		}
	);
	if (!confirmed) return;

	try {
		await request.del(["tracker.time.destroy", entry.id]);
		deleted.value = true;
		emit("delete");
	} catch {
		toast.error(
			"Unable to delete time entry",
			"See the browser console for more information."
		);
	}
}
</script>
