document.addEventListener('DOMContentLoaded', () => {
	const deleteForms = document.querySelectorAll('form.delete');
	for (const form of deleteForms) {
		form.addEventListener('seamlessSuccess', () => {
			form.closest('tr').remove();
		});
	}

	const updateForms = document.querySelectorAll('form.update');
	for (const form of updateForms) {
		const updateBtn = form.querySelector('button[type="submit"]');
		const row = form.closest('tr');
		const nameIpt = row.querySelector('input[name="name"]');
		const descIpt = row.querySelector('textarea[name="description"]');
		const hoursIpt = row.querySelector('input[name="hours"]');

		updateBtn.disabled = true;
		nameIpt.addEventListener('input', () => {
			updateBtn.disabled = false;
		});
		descIpt.addEventListener('input', () => {
			updateBtn.disabled = false;
		});
		hoursIpt.addEventListener('input', () => {
			updateBtn.disabled = false;
		});
		form.addEventListener('seamlessSuccess', () => {
			setTimeout(() => {
				updateBtn.disabled = true;
			}, 0);
		});
	}

	const createForm = document.getElementById('rewardCreate');
	const createBtn = createForm.querySelector('button[type="submit"]');
	const nameIpt = document.getElementById('rewardName');
	const descIpt = document.getElementById('rewardDescription');
	const hoursIpt = document.getElementById('rewardHours');

	createBtn.disabled = true;
	nameIpt.addEventListener('input', updateCreateBtn);
	descIpt.addEventListener('input', updateCreateBtn);
	hoursIpt.addEventListener('input', updateCreateBtn);
	createForm.addEventListener('seamlessSuccess', () => {
		window.location.reload();
	});

	function updateCreateBtn() {
		createBtn.disabled =
			!nameIpt.value?.trim() || !descIpt.value?.trim() || !hoursIpt.value?.trim() || !hoursIpt.checkValidity();
	}
});
