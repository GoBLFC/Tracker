document.addEventListener('DOMContentLoaded', () => {
	const deleteForms = document.querySelectorAll('form.delete');
	for(const form of deleteForms) {
		form.addEventListener('seamlessSuccess', () => { form.closest('tr').remove(); });
	}

	const updateForms = document.querySelectorAll('form.update');
	for(const form of updateForms) {
		const updateBtn = form.querySelector('button[type="submit"]');
		const nameIpt = form.closest('tr').querySelector('input[name="name"]');

		updateBtn.disabled = true;
		nameIpt.addEventListener('input', () => { updateBtn.disabled = false; });
		form.addEventListener('seamlessSuccess', () => {
			setTimeout(() => { updateBtn.disabled = true; }, 0);
		});
	}

	const createForm = document.getElementById('evtCreate');
	const createBtn = createForm.querySelector('button[type="submit"]');
	const nameIpt = document.getElementById('evtName');

	createBtn.disabled = true;
	nameIpt.addEventListener('input', () => { createBtn.disabled = !nameIpt.value.trim(); });
	createForm.addEventListener('seamlessSuccess', () => { window.location.reload(); });
});
