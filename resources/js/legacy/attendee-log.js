import { setupSeamlessForm } from './seamless-forms';
import successSoundFile from '../../audio/success.ogg';
import success2SoundFile from '../../audio/success2.ogg';
import alertSoundFile from '../../audio/alert.ogg';

document.addEventListener('DOMContentLoaded', () => {
	const successSound = new Audio(successSoundFile);
	const success2Sound = new Audio(success2SoundFile);
	const alertSound = new Audio(alertSoundFile);

	const deleteForms = document.querySelectorAll('form.delete');
	for (const form of deleteForms) {
		form.addEventListener('seamlessSuccess', () => removeUserFromTable(form));
	}

	const logForm = document.getElementById('userLog');
	const logBtn = logForm.querySelector('button[type="submit"]');
	const logBadgeIdIpt = document.getElementById('logBadgeId');

	logBtn.disabled = true;
	logBadgeIdIpt.addEventListener('input', updateLogBtn);
	logForm.addEventListener('seamlessSuccess', addUserToTable);
	logForm.addEventListener('seamlessError', alertForError);

	function updateLogBtn() {
		logBtn.disabled = !logBadgeIdIpt.value?.trim();
	}

	const gatekeeperForm = document.getElementById('gatekeeperAdd');
	if (gatekeeperForm) {
		const gatekeeperBtn = gatekeeperForm.querySelector('button[type="submit"]');
		const gatekeeperBadgeIdIpt = document.getElementById('gatekeeperBadgeId');

		gatekeeperBtn.disabled = true;
		gatekeeperBadgeIdIpt.addEventListener('input', updateGatekeeperBtn);
		gatekeeperForm.addEventListener('seamlessSuccess', (evt) => addUserToTable(evt, false));

		function updateGatekeeperBtn() {
			gatekeeperBtn.disabled = !gatekeeperBadgeIdIpt.value?.trim();
		}
	}

	const usersCardBody = document.getElementById('users-body');
	const noUsersCardBody = document.getElementById('users-body-empty');

	function addUserToTable(evt, fromAttendee = true) {
		const {
			user: { id, badge_id: badgeId, badge_name: badgeName },
			type,
			logged_at: logged,
		} = evt.detail.response;

		const row = document.createElement('tr');

		const idCell = document.createElement('th');
		idCell.textContent = badgeId;
		idCell.scope = 'row';
		row.appendChild(idCell);

		const nameCell = document.createElement('td');
		nameCell.textContent = badgeName;
		row.appendChild(nameCell);

		const typeBadge = document.createElement('span');
		typeBadge.classList.add('badge', 'rounded-pill', `text-bg-${type === 'gatekeeper' ? 'warning' : 'secondary'}`);
		typeBadge.textContent = `${type.charAt(0).toUpperCase()}${type.slice(1)}`;

		const typeCell = document.createElement('td');
		typeCell.appendChild(typeBadge);
		row.appendChild(typeCell);

		const loggedCell = document.createElement('td');
		loggedCell.textContent = logged;
		row.appendChild(loggedCell);

		const deleteForm = document.createElement('form');
		deleteForm.action = attendeeLogsUsersDestroyUrl
			.replace(/attendee-log-id/, attendeeLogId)
			.replace(/user-id/, id);
		deleteForm.addEventListener('seamlessSuccess', () => removeUserFromTable(deleteForm));

		const methodIpt = document.createElement('input');
		methodIpt.type = 'hidden';
		methodIpt.name = '_method';
		methodIpt.value = 'DELETE';
		deleteForm.appendChild(methodIpt);

		const csrfIpt = document.createElement('input');
		csrfIpt.type = 'hidden';
		csrfIpt.name = '_token';
		csrfIpt.value = _token;
		deleteForm.appendChild(csrfIpt);

		const deleteBtn = document.createElement('button');
		deleteBtn.textContent = 'Delete';
		deleteBtn.type = 'submit';
		deleteBtn.classList.add('btn', 'btn-sm', 'btn-danger', 'float-end');
		deleteBtn.setAttribute('data-success', `Deleted ${type}.`);
		deleteBtn.setAttribute('data-confirm-title', `Delete ${type}?`);
		deleteBtn.setAttribute('data-confirm-text', badgeName);
		deleteForm.appendChild(deleteBtn);
		setupSeamlessForm(deleteForm);

		const deleteCell = document.createElement('td');
		deleteCell.appendChild(deleteForm);
		row.appendChild(deleteCell);

		usersCardBody.querySelector('tbody').appendChild(row);
		usersCardBody.classList.remove('d-none');
		noUsersCardBody.classList.add('d-none');

		if (fromAttendee) {
			logBadgeIdIpt.value = '';
			logBadgeIdIpt.focus();
			successSound.play();
			setTimeout(updateLogBtn, 0);
		}
	}

	function removeUserFromTable(form) {
		form.closest('tr').remove();

		if (usersCardBody.querySelector('tbody').childElementCount === 0) {
			usersCardBody.classList.add('d-none');
			noUsersCardBody.classList.remove('d-none');
		}
	}

	function alertForError(evt) {
		logBadgeIdIpt.value = '';
		logBadgeIdIpt.focus();
		setTimeout(updateLogBtn, 0);

		if (evt.detail?.error?.message?.includes('already present')) success2Sound.play();
		else alertSound.play();
	}
});
