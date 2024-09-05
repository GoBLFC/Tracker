import { TempusDominus, Namespace as TDNamespace } from '@eonasdan/tempus-dominus';

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
		const start = row.querySelector('.bonusStart');
		const stop = row.querySelector('.bonusStop');
		const modifier = row.querySelector('input[name="modifier"]');
		const departments = row.querySelector('select[name="departments[]"]');

		const timeStart = new TempusDominus(start, { localization: { format: 'yyyy-MM-dd hh:mm:ss T' } });
		const timeStop = new TempusDominus(stop, { localization: { format: 'yyyy-MM-dd hh:mm:ss T' } });
		timeStart.subscribe(TDNamespace.events.change, (evt) => {
			timeStop.updateOptions({ restrictions: { minDate: evt.date } });
		});

		updateBtn.disabled = true;
		timeStart.subscribe(TDNamespace.events.change, () => {
			updateBtn.disabled = false;
		});
		timeStop.subscribe(TDNamespace.events.change, () => {
			updateBtn.disabled = false;
		});
		modifier.addEventListener('input', () => {
			updateBtn.disabled = false;
		});
		departments.addEventListener('input', () => {
			updateBtn.disabled = false;
		});
		form.addEventListener('seamlessSuccess', () => {
			setTimeout(() => {
				updateBtn.disabled = true;
			}, 0);
		});
	}

	const createForm = document.getElementById('bonusCreate');
	const createBtn = createForm.querySelector('button[type="submit"]');
	const start = document.getElementById('bonusStart');
	const stop = document.getElementById('bonusStop');
	const modifier = document.getElementById('bonusModifier');
	const departments = document.getElementById('bonusDepartments');

	const timeStart = new TempusDominus(start, { localization: { format: 'yyyy-MM-dd hh:mm:ss T' } });
	const timeStop = new TempusDominus(stop, { localization: { format: 'yyyy-MM-dd hh:mm:ss T' } });
	timeStart.subscribe(TDNamespace.events.change, (evt) => {
		timeStop.updateOptions({ restrictions: { minDate: evt.date } });
	});

	createBtn.disabled = true;
	timeStart.subscribe(TDNamespace.events.change, updateCreateBtn);
	timeStop.subscribe(TDNamespace.events.change, updateCreateBtn);
	modifier.addEventListener('input', updateCreateBtn);
	departments.addEventListener('input', updateCreateBtn);
	createForm.addEventListener('seamlessSuccess', () => {
		window.location.reload();
	});

	function updateCreateBtn() {
		createBtn.disabled =
			!timeStart.dates.picked[0] ||
			!timeStop.dates.picked[0] ||
			!modifier.checkValidity() ||
			departments.selectedOptions.length < 1;
	}
});
