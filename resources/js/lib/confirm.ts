import { useConfirm as usePrimeVueConfirm } from 'primevue/useconfirm';
import type { ButtonProps } from 'primevue/button';

/**
 * Composable for displaying confirmation dialogs
 */
export function useConfirm() {
	const pvConfirm = usePrimeVueConfirm();

	/**
	 * Displays a confirmation dialog or popup and waits for an answer
	 */
	function confirm(title: string, text: string, options?: ConfirmOptions): Promise<boolean>;
	function confirm(text: string, options?: ConfirmOptions): Promise<boolean>;
	function confirm(
		titleOrText: string,
		textOrOptions?: string | ConfirmOptions,
		options?: ConfirmOptions,
	): Promise<boolean> {
		const title = typeof textOrOptions === 'string' ? titleOrText : undefined;
		const text = typeof textOrOptions === 'string' ? textOrOptions : titleOrText;
		let { accept, reject, target } = options ?? (typeof textOrOptions === 'object' ? textOrOptions : {});

		if (!reject) reject = {};
		if (!reject.label) reject.label = 'Cancel';
		if (!reject.severity) reject.severity = 'secondary';

		return new Promise((resolve) => {
			pvConfirm.require({
				header: title,
				message: text,
				acceptProps: accept,
				rejectProps: reject,
				target,

				// Timeouts are used before resolving on accept/reject because PrimeVue's ConfirmPopup component
				// seems to have a bug that causes the popup to not automatically hide when the target element
				// rerenders during the same tick resulting from one of the buttons being clicked via a key press
				accept() {
					setTimeout(() => resolve(true), 0);
				},
				reject() {
					setTimeout(() => resolve(false), 0);
				},
				onHide() {
					resolve(false);
				},
			});
		});
	}

	return { confirm };
}

export interface ConfirmOptions {
	accept?: ButtonProps;
	reject?: ButtonProps;
	target?: HTMLElement;
}
