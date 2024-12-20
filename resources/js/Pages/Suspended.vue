<template>
	<BasicPage
		title="Not Permitted"
		severity="warn"
		:icon="faTriangleExclamation"
	>
		There is a hold on your volunteer account. Please talk to a Volunteer
		Manager at the volunteer desk.
	</BasicPage>
</template>

<script setup lang="ts">
import { faTriangleExclamation } from '@fortawesome/free-solid-svg-icons';
import BasicPage from '../Components/BasicPage.vue';
</script>
