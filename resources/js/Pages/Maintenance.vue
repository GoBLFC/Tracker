<template>
	<BasicPage title="Maintenace" :icon="faHardHat">
		The site is disabled for maintenance. Check back again shortly.
	</BasicPage>
</template>

<script setup lang="ts">
import { faHardHat } from '@fortawesome/free-solid-svg-icons';
import BasicPage from '../Components/BasicPage.vue';
</script>
