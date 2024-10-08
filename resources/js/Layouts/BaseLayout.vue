<template>
	<slot />
</template>

<script setup>
import { useToast } from '../lib/toast';

useToast({ flashes: true });
</script>
