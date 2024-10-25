import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type SharedProps from './SharedProps';

// @ts-ignore: Missing typings for the dist file
import Swal from 'sweetalert2/dist/sweetalert2.js';

export const Toast = Swal.mixin({
	toast: true,
	position: 'top-end',
	showConfirmButton: false,
	timer: 4000,
	timerProgressBar: true,
	showClass: {
		popup: 'animate__animated animate__slideInDown animate__faster',
	},
});

/**
 * Displays toast messages
 * @param {Object} [options]
 * @param {boolean} [options.flashes=false] Whether to automatically handle any session flash messages
 */
export function useToast({ flashes = false } = {}) {
	if (flashes) {
		const page = usePage<SharedProps>();

		onMounted(() => {
			showFlashes(page.props.flash);
		});

		watch(() => page.props.flash, showFlashes);
	}

	/**
	 * Temporarily displays a success message
	 */
	function success(text: string): void;
	function success(title: string, text: string): void;
	function success(titleOrText: string, text?: string): void {
		Toast.fire({
			title: text ? titleOrText : undefined,
			text: text ?? titleOrText,
			icon: 'success',
		});
	}

	/**
	 * Temporarily displays an error message
	 */
	function error(text: string): void;
	function error(title: string, text: string): void;
	function error(titleOrText: string, text?: string): void {
		Toast.fire({
			title: text ? titleOrText : undefined,
			text: text ?? titleOrText,
			icon: 'error',
		});
	}

	/**
	 * Displays an action confirmation dialog
	 * @returns Whether the user confirmed the action
	 */
	async function confirm(text: string): Promise<boolean>;
	async function confirm(title: string, text: string): Promise<boolean>;
	async function confirm(
		title: string,
		text: string,
		{ icon, showCancel, confirmText }: { icon?: string; showCancel?: boolean; confirmText?: string },
	): Promise<boolean>;
	async function confirm(
		titleOrText: string,
		text?: string,
		{ icon, showCancel, confirmText }: { icon?: string; showCancel?: boolean; confirmText?: string } = {},
	): Promise<boolean> {
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
