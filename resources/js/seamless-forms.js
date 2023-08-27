import { Toast, sendRequest, spinner } from "./shared";

document.addEventListener('DOMContentLoaded', () => {
	const forms = document.querySelectorAll('form.seamless');
	for(const form of forms) {
		const submitBtn = form.querySelector('button[type="submit"]');
		form.addEventListener('submit', async event => {
			event.preventDefault();

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

				// Make the request
				await sendRequest(form.action, {
					method: data.has('_method') ? data.get('_method').toUpperCase() : 'POST',
					body: JSON.stringify(Object.fromEntries(data)),
				});

				// Invert the state if applicable
				if(hasState) state = !state;

				// Display a success toast
				Toast.fire({
					text: submitBtn.getAttribute(hasState ? `data-success-${state}` : 'data-success'),
					icon: 'success',
				});
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
});
