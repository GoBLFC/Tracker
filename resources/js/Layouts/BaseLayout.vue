<template>
	<div class="relative -z-50">
		<slot />

		<Toast />
		<ConfirmPopup />
	</div>
</template>

<script setup lang="ts">
import { useToast } from '../lib/toast';

useToast({ flashes: true });
</script>
