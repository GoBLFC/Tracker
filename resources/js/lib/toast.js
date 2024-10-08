import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { Toast } from '../legacy/shared';

/**
 * Displays toast messages
 * @param {Object} [options]
 * @param {boolean} [options.flashes] Whether to automatically handle any session flash messages
 */
export function useToast({ flashes = false } = {}) {
	if (flashes) {
		const page = usePage();

		onMounted(() => {
			showFlashes(page.props.flash);
		});

		watch(() => page.props.flash, showFlashes);
	}

	/**
	 * Temporarily displays a success message
	 * @param {string} titleOrText
	 * @param {string} [text]
	 */
	function success(titleOrText, text) {
		Toast.fire({
			title: text ? titleOrText : undefined,
			text: text ?? titleOrText,
			icon: 'success',
		});
	}

	/**
	 * Temporarily displays an error message
	 * @param {string} titleOrText
	 * @param {string} [text]
	 */
	function error(titleOrText, text) {
		Toast.fire({
			title: text ? titleOrText : undefined,
			text: text ?? titleOrText,
			icon: 'error',
		});
	}

	/**
	 * Displays an action confirmation dialog
	 * @param {string} titleOrText
	 * @param {string} [text]
	 * @param {Object} [options]
	 * @param {string} [options.icon]
	 * @param {boolean} [options.cancel]
	 * @param {string} [options.confirmText]
	 * @returns {boolean} Whether the user confirmed the action
	 */
	async function confirm(titleOrText, text, { icon, showCancel, confirmText } = {}) {
		const result = await Swal.fire({
			title: text ? titleOrText : undefined,
			text: text ?? titleOrText,
			icon,
			showCancelButton: showCancel,
			focusCancel: showCancel,
			confirmButtonText: confirmText,
		});
		return result.isConfirmed;
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
		confirm,
	};
}
