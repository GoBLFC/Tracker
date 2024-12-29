<template>
	<Dialog
		modal
		header="Link Telegram"
		class="w-full sm:w-[32rem] mx-2"
		:dismissable-mask="true"
		@show="renderQrCode"
	>
		<canvas
			ref="canvas"
			class="block mx-auto mb-8 rounded-md border-2 border-surface-200 dark:border-surface-600 object-scale-down"
		></canvas>

		<p class="mb-4">
			Scanning the above QR code will give you a URL to add the Telegram
			bot and link your Telegram profile to your volunteer account
			automatically.
			<template v-if="!isKiosk">
				<span class="me-1">
					If you can't scan the QR code, use this link instead:
				</span>
				<a :href="setupUrl" target="_blank" class="text-nowrap"
					><FontAwesomeIcon :icon="faTelegram" /> Add Telegram Bot</a
				>
			</template>
		</p>

		<p class="mb-2">This bot can provide you:</p>
		<ul class="mb-4 ps-6 list-disc list-inside">
			<li>Hours clocked</li>
			<li>Reward list</li>
			<li>Quick sign-in codes</li>
		</ul>

		<p class="mb-2">This bot will also:</p>
		<ul class="ps-6 list-disc list-inside">
			<li>Remind you when you're eligible for a reward</li>
			<li>Confirm that you've claimed a reward</li>
			<li>Notify you if you've forgotten to check out for a shift</li>
		</ul>
	</Dialog>
</template>

<script setup lang="ts">
import { useTemplateRef } from 'vue';
import QRCode from 'qrcode';
import { useToast } from '@/lib/toast';
import { useAppSettings } from '@/lib/settings';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTelegram } from '@fortawesome/free-brands-svg-icons';

const { setupUrl } = defineProps<{ setupUrl: string }>();

const canvas = useTemplateRef('canvas');
const { isKiosk } = useAppSettings();
const toast = useToast();

let qrRendered = false;

/**
 * Renders a QR code into the canvas with the setup URL encoded into it
 */
async function renderQrCode() {
	try {
		await QRCode.toCanvas(canvas.value!, setupUrl, {
			errorCorrectionLevel: 'medium',
			margin: 2,
			width: canvas.value!.width,
		});
		qrRendered = true;
	} catch (err) {
		console.error('Error generating Telegram QR code', err);
		toast.error('Failed to generate QR code', 'See the browser console for more information.');
	}
}
</script>
