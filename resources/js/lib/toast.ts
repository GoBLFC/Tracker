import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast as usePrimeVueToast } from 'primevue/usetoast';
import type { ToastMessageOptions } from 'primevue/toast';
import type SharedProps from './SharedProps';

/**
 * Displays toast messages
 * @param {Object} [options]
 * @param {boolean} [options.flashes=false] Whether to automatically handle any session flash messages
 */
export function useToast({ flashes = false } = {}) {
	const toast = usePrimeVueToast();

	if (flashes) {
		const page = usePage<SharedProps>();

		onMounted(() => {
			showFlashes(page.props.flash);
		});

		watch(() => page.props.flash, showFlashes);
	}

	/**
	 * Shows a single toast message
	 */
	function show(title: string, text: string, type: ToastMessageOptions['severity']): void {
		toast.add({
			summary: title,
			detail: text,
			severity: type,
			life: 4000,
		});
	}

	/**
	 * Temporarily displays a success message
	 */
	function success(text: string): void;
	function success(title: string, text: string): void;
	function success(titleOrText: string, text?: string): void {
		show(text ? titleOrText : 'Success', text ?? titleOrText, 'success');
	}

	/**
	 * Temporarily displays an error message
	 */
	function error(text: string): void;
	function error(title: string, text: string): void;
	function error(titleOrText: string, text?: string): void {
		show(text ? titleOrText : 'Error', text ?? titleOrText, 'error');
	}

	/**
	 * Displays success and error messages as needed from a session flash object
	 */
	function showFlashes(flash: Flashes): void {
		if (flash.success) success(flash.success);
		if (flash.error) error(flash.error);
	}

	return {
		success,
		error,
		confirm,
	};
}

export interface Flashes {
	success: string | null;
	error: string | null;
}
