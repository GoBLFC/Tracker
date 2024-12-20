<template>
	<div class="h-full flex flex-col justify-center items-center gap-8">
		<Head title="Not Permitted" />

		<FontAwesomeIcon class="text-6xl" :icon="faTriangleExclamation" />

		<h1 class="text-3xl text-center font-light">Not Permitted</h1>

		<Message severity="warn">
			There is a hold on your volunteer account. Please talk to a
			Volunteer Manager at the volunteer desk.
		</Message>
	</div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTriangleExclamation } from '@fortawesome/free-solid-svg-icons';
</script>
