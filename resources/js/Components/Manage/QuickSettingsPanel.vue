<template>
	<Panel header="Quick Settings">
		<dl>
			<div class="flex items-center gap-4">
				<dt>
					<KioskToggleSwitch :aria-labelledby="kioskSettingId" />
				</dt>
				<dd>
					<p class="text-lg font-semibold" :id="kioskSettingId">
						Kiosk authorization
					</p>
					<p>
						Authorizing this device as a kiosk will allow non-staff
						volunteers to check in or out on this device. This is
						required when setting up dedicated devices pre-con for
						checking in or out. Kiosks remain authorized for
						{{ kioskLifetimeText }}.
					</p>
				</dd>
			</div>
		</dl>
	</Panel>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue';
import humanizeDuration from 'humanize-duration';
import { useAppSettings } from '@/lib/settings';

import KioskToggleSwitch from './KioskToggleSwitch.vue';

const { kioskLifetime } = useAppSettings();

const kioskSettingId = useId();
const kioskLifetimeText = computed(() => humanizeDuration(kioskLifetime.value * 1000 * 60));
</script>
