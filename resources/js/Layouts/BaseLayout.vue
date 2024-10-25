<template>
	<slot />
</template>

<script setup lang="ts">
import { useToast } from '../lib/toast';

useToast({ flashes: true });
</script>
