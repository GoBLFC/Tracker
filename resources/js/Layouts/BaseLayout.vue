<template>
	<slot />

	<Toast />
	<ConfirmPopup />
</template>

<script setup lang="ts">
import { watchEffect } from 'vue';
import { useLocalSettings } from '@/lib/settings';
import { useTheme } from '@/lib/media-query';
import { useToast } from '@/lib/toast';

const { theme: systemTheme } = useTheme();
const { theme: preferredTheme } = useLocalSettings();
useToast({ flashes: true });

watchEffect(() => {
	const dark = preferredTheme.value === 'dark' || (preferredTheme.value === 'system' && systemTheme.value === 'dark');

	if (dark) document.documentElement.classList.add('dark');
	else document.documentElement.classList.remove('dark');
});
</script>
