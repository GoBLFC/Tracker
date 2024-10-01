import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Toast } from '../legacy/shared';

/**
 * Displays toast messages
 * @param {Object} [options]
 * @param {boolean} [options.flashes] Whether to automatically handle any session flash messages
 */
export function useToast({ flashes = true } = {}) {
	if (flashes) {
		const page = usePage();

		onMounted(() => {
			showFlashes(page.props.flash);
		});

		watch(() => page.props.flash, showFlashes);
	}

	/**
	 * Temporarily displays a success message
	 */
	function success(text) {
		Toast.fire({
			text,
			icon: 'success',
		});
	}

	/**
	 * Temporarily displays an error message
	 */
	function error(text) {
		Toast.fire({
			text,
			icon: 'error',
		});
	}

	/**
	 * Displays success and error messages as needed from a session flash object
	 */
	function showFlashes(flash) {
		if (flash.success) success(flash.success);
		if (flash.error) error(flash.error);
	}

	return {
		success,
		error,
	};
}
