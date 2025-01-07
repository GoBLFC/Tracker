<template>
	<BasicPage title="Maintenance" :icon="faHardHat">
		The site is disabled for maintenance. Check back again shortly.
	</BasicPage>
</template>

<script setup lang="ts">
import { faHardHat } from '@fortawesome/free-solid-svg-icons';
import BasicPage from '@/Components/App/BasicPage.vue';
</script>
