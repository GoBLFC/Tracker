<template>
	<Dialog
		v-model:visible="isVisible"
		modal
		:show-header="false"
		:dismissable-mask="true"
		class="w-4/5 sm:w-64 mx-2"
	>
		<div class="h-64 flex flex-col justify-center items-center gap-12">
			<p v-if="title" class="text-2xl font-light">{{ title }}</p>

			<div class="swal2-icon swal2-success">
				<div class="swal2-success-circular-line-left"></div>
				<span class="swal2-success-line-tip"></span>
				<span class="swal2-success-line-long"></span>
				<div class="swal2-success-ring"></div>
				<div class="swal2-success-fix"></div>
				<div class="swal2-success-circular-line-right"></div>
			</div>
		</div>
	</Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const { closeDelay = 2000 } = defineProps<{
	title?: string;
	closeDelay?: number;
}>();
defineExpose({ show });

const isVisible = ref(false);

/**
 * Displays the success dialog
 */
function show() {
	isVisible.value = true;
	setTimeout(() => {
		isVisible.value = false;
	}, closeDelay);
}
</script>

<style>
/* Styles for this component are from the sweetalert2 library, licensed under the MIT license.
 * https://sweetalert2.github.io/
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Tristan Edwards & Limon Monte
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
.swal2-icon {
	display: flex;
	position: relative;
	box-sizing: content-box;
	justify-content: center;
	width: 5em;
	height: 5em;
	border: 0.25em solid rgba(0, 0, 0, 0);
	border-radius: 50%;
	line-height: 5em;
	cursor: default;
	user-select: none;
}

.swal2-success {
	border-color: #a5dc86;
	color: #a5dc86;
}

.swal2-success-circular-line-left,
.swal2-success-circular-line-right,
.swal2-success-fix {
	background-color: var(--p-dialog-background);
}

.swal2-success-circular-line-left,
.swal2-success-circular-line-right {
	position: absolute;
	width: 3.75em;
	height: 7.5em;
	border-radius: 50%;
}

.swal2-success-circular-line-left {
	top: -0.4375em;
	left: -2.0635em;
	transform: rotate(-45deg);
	transform-origin: 3.75em 3.75em;
	border-radius: 7.5em 0 0 7.5em;
}

.swal2-success-circular-line-right {
	top: -0.6875em;
	left: 1.875em;
	transform: rotate(-45deg);
	transform-origin: 0 3.75em;
	border-radius: 0 7.5em 7.5em 0;
	animation: swal2-rotate-success-circular-line 4.25s ease-in;
}

.swal2-success-line-tip,
.swal2-success-line-long {
	display: block;
	position: absolute;
	z-index: 2;
	height: 0.3125em;
	border-radius: 0.125em;
	background-color: #a5dc86;
}

.swal2-success-line-tip {
	top: 2.875em;
	left: 0.8125em;
	width: 1.5625em;
	transform: rotate(45deg);
	animation: swal2-animate-success-line-tip 0.75s;
}

.swal2-success-line-long {
	top: 2.375em;
	right: 0.5em;
	width: 2.9375em;
	transform: rotate(-45deg);
	animation: swal2-animate-success-line-long 0.75s;
}

.swal2-success-ring {
	position: absolute;
	z-index: 2;
	top: -0.25em;
	left: -0.25em;
	box-sizing: content-box;
	width: 100%;
	height: 100%;
	border: 0.25em solid rgba(165, 220, 134, 0.3);
	border-radius: 50%;
}

.swal2-success-fix {
	position: absolute;
	z-index: 1;
	top: 0.5em;
	left: 1.625em;
	width: 0.4375em;
	height: 5.625em;
	transform: rotate(-45deg);
}

@keyframes swal2-animate-success-line-tip {
	0% {
		top: 1.1875em;
		left: 0.0625em;
		width: 0;
	}
	54% {
		top: 1.0625em;
		left: 0.125em;
		width: 0;
	}
	70% {
		top: 2.1875em;
		left: -0.375em;
		width: 3.125em;
	}
	84% {
		top: 3em;
		left: 1.3125em;
		width: 1.0625em;
	}
	100% {
		top: 2.8125em;
		left: 0.8125em;
		width: 1.5625em;
	}
}

@keyframes swal2-animate-success-line-long {
	0% {
		top: 3.375em;
		right: 2.875em;
		width: 0;
	}
	65% {
		top: 3.375em;
		right: 2.875em;
		width: 0;
	}
	84% {
		top: 2.1875em;
		right: 0;
		width: 3.4375em;
	}
	100% {
		top: 2.375em;
		right: 0.5em;
		width: 2.9375em;
	}
}

@keyframes swal2-rotate-success-circular-line {
	0% {
		transform: rotate(-45deg);
	}
	5% {
		transform: rotate(-45deg);
	}
	12% {
		transform: rotate(-405deg);
	}
	100% {
		transform: rotate(-405deg);
	}
}
</style>
