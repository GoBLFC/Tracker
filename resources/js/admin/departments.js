document.addEventListener('DOMContentLoaded', () => {
	const deleteForms = document.querySelectorAll('form.delete');
	for(const form of deleteForms) {
		form.addEventListener('seamlessSuccess', () => { form.closest('tr').remove(); });
	}

	const updateForms = document.querySelectorAll('form.update');
	for(const form of updateForms) {
		const updateBtn = form.querySelector('button[type="submit"]');
		const nameIpt = form.closest('tr').querySelector('input[name="name"]');
		const hideChk = form.closest('tr').querySelector('input[type="checkbox"]');

		updateBtn.disabled = true;
		nameIpt.addEventListener('input', () => { updateBtn.disabled = false; });
		hideChk.addEventListener('input', () => { updateBtn.disabled = false; });
		form.addEventListener('seamlessSuccess', () => {
			setTimeout(() => { updateBtn.disabled = true; }, 0);
		});
	}

	const createForm = document.getElementById('dptCreate');
	const createBtn = createForm.querySelector('button[type="submit"]');
	const nameIpt = document.getElementById('dptName');

	createBtn.disabled = true;
	nameIpt.addEventListener('input', () => { createBtn.disabled = !nameIpt.value.trim(); });
	createForm.addEventListener('seamlessSuccess', () => { window.location.reload(); });
});
