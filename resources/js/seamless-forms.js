import Swal from 'sweetalert2/dist/sweetalert2.js';
import { Toast, sendRequest, spinner } from "./shared";

document.addEventListener('DOMContentLoaded', () => {
	const forms = document.querySelectorAll('form.seamless');
	for(const form of forms) setupSeamlessForm(form);
});

/**
 * Sets up a seamless form
 * @param {HTMLFormElement} form
 */
export function setupSeamlessForm(form) {
	const submitBtn = form.querySelector('button[type="submit"]');
	form.addEventListener('submit', async event => {
		event.preventDefault();

		// Prompt for confirmation
		const confirmTitle = submitBtn.getAttribute('data-confirm-title');
		if(confirmTitle) {
			const result = await Swal.fire({
				title: confirmTitle,
				text: submitBtn.getAttribute('data-confirm-text'),
				icon: 'warning',
				showCancelButton: true,
				focusCancel: true,
				confirmButtonText: submitBtn.getAttribute('data-confirm-button') ?? submitBtn.textContent,
			});
			if(!result.isConfirmed) return;
		}

		// Get the current state, if any
		let state = submitBtn.getAttribute('data-state');
		if(typeof state === 'string') state = JSON.parse(state);
		const hasState = state !== null;

		// Set the loading label for the button and store the original text if it isn't using state
		submitBtn.disabled = true;
		if(hasState) {
			submitBtn.textContent = submitBtn.getAttribute(`data-label-loading-${!state}`);
			submitBtn.prepend(spinner('me-1'));
		} else {
			submitBtn.setAttribute('data-original-text', submitBtn.textContent);
			submitBtn.replaceChildren(spinner('mx-2'));
		}

		try {
			// Get the form's data and add the correct state input if applicable
			const data = new FormData(form);
			if(hasState) data.set(submitBtn.getAttribute('data-state-input'), Number(!state));
			const dataObj = formDataToObject(data);

			// Make the request
			const response = await sendRequest(form.action, {
				method: data.has('_method') ? data.get('_method').toUpperCase() : 'POST',
				body: JSON.stringify(dataObj),
			});

			// Invert the state if applicable
			if(hasState) state = !state;

			// Display a success toast
			const success = submitBtn.getAttribute(hasState ? `data-success-${state}` : 'data-success');
			if(success) {
				Toast.fire({
					text: success,
					icon: 'success',
				});
			}

			// Fire an event for the success
			form.dispatchEvent(new CustomEvent('seamlessSuccess', { detail: { input: dataObj, response, state } }));
		} finally {
			// Store the state and swap the classes for it if applicable
			if(hasState) {
				submitBtn.classList.replace(
					submitBtn.getAttribute(`data-class-${!state}`),
					submitBtn.getAttribute(`data-class-${state}`),
				);
				submitBtn.setAttribute('data-state', state);
			}

			// Reset the button
			submitBtn.textContent = submitBtn.getAttribute(hasState ? `data-label-${state}` : 'data-original-text');
			submitBtn.disabled = false;
		}
	});
}

/**
 * Serializes form data into a plain object, taking arrays into account
 * @param {FormData} data
 * @returns {Object}
 */
export function formDataToObject(data) {
	const dataObj = {};
	for(const [key, val] of data) {
		if(key.endsWith('[]')) {
			const plainKey = key.slice(0, -2);
			if(!dataObj[plainKey]) dataObj[plainKey] = [];
			dataObj[plainKey].push(val);
			continue;
		}

		dataObj[key] = val;
	}
	return dataObj;
}
