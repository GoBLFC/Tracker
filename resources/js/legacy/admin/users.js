import { findUserByBadgeId, sendGetRequest, sendPatchRequest, spinner, Toast } from '../shared.js';

document.addEventListener('DOMContentLoaded', () => {
	const badgeNumberInput = document.getElementById('badgeNumber');
	const roleButtons = document.querySelectorAll('button[data-role]');

	// Make buttons go
	for (const btn of roleButtons)
		btn.addEventListener('click', () => {
			handleSetRole(btn);
		});

	// Enable/disable the buttons depending on whether a badge number has been provided
	badgeNumberInput.addEventListener('input', () => {
		const value = badgeNumberInput.value.trim();
		for (const btn of roleButtons) btn.disabled = !value;
	});
});

/**
 * Handles the click of a role-setting button
 * @param {HTMLButtonElement} btn
 */
async function handleSetRole(btn) {
	const role = Number(btn.getAttribute('data-role'));
	const badgeId =
		role === 0
			? btn.closest('tr').getAttribute('data-user-id')
			: Number(document.getElementById('badgeNumber').value.trim());

	// Disable all the buttons
	const roleButtons = document.querySelectorAll('button[data-role]');
	for (const btn of roleButtons) btn.disabled = true;

	// Store the original label and show the loading indicator
	btn.setAttribute('data-original-text', btn.textContent);
	btn.prepend(spinner('me-2'));

	try {
		// Make the request and add/remove rows as necessary
		const user = await setUserRole(badgeId, role);
		removeUserFromTables(user);
		addUserToTable(user);
	} finally {
		// Reset the buttons
		for (const btn of roleButtons) btn.disabled = false;
		btn.textContent = btn.getAttribute('data-original-text');
	}
}

/**
 * Sends a request to update the role of a user by their UUID or badge ID
 * @param {string|number} badgeId
 * @param {number|string} role
 * @returns {Object} Updated user object
 */
export async function setUserRole(badgeId, role) {
	// Search for the user, then update their role and notify upon success
	const uuid = typeof badgeId === 'string' ? badgeId : (await findUserByBadgeId(badgeId)).id;
	const { user } = await sendPatchRequest(userUpdateUrl.replace(/id/, uuid), { role });
	Toast.fire({
		text: `Set ${user.badge_name ?? user.username}'s role to ${roles[user.role]}.`,
		icon: 'success',
	});

	return user;
}

/**
 * Adds a user to the correct table
 * @param {Object} user
 */
function addUserToTable(user) {
	// Build the row content
	const row = document.createElement('tr');
	row.setAttribute('data-user-id', user.id);

	const idCell = document.createElement('th');
	idCell.textContent = user.badge_id;

	const usernameCell = document.createElement('td');
	usernameCell.textContent = user.username;

	const realNameCell = document.createElement('td');
	realNameCell.textContent = `${user.first_name} ${user.last_name}`;

	const actionsCell = document.createElement('td');
	const deleteBtn = document.createElement('button');
	deleteBtn.textContent = 'Remove';
	deleteBtn.classList.add('btn', 'btn-sm', 'btn-danger', 'float-end');
	deleteBtn.setAttribute('data-role', 0);
	deleteBtn.addEventListener('click', () => {
		handleSetRole(deleteBtn);
	});
	actionsCell.append(deleteBtn);

	row.append(idCell, usernameCell, realNameCell, actionsCell);

	const tbody = document.querySelector(`table[data-role="${user.role}"] tbody`);
	tbody.append(row);

	// If this is the first row, show the table and hide the no items placeholder
	if (tbody.childElementCount === 1) {
		const tableCardBody = tbody.closest('.card-body');
		const placeholderCardBody = tableCardBody.parentElement.querySelector('.card-body.placeholder');
		tableCardBody.classList.remove('d-none');
		placeholderCardBody.classList.add('d-none');
	}
}

/**
 * Removes a user from all tables
 * @param {Object} user
 */
function removeUserFromTables(user) {
	const rows = document.querySelectorAll(`tr[data-user-id="${user.id}"]`);
	for (const row of rows) {
		// Get the parent tbody then remove the row
		const tbody = row.closest('tbody');
		row.remove();

		// If there aren't any more rows, hide the table and show the no items placeholder
		if (tbody.childElementCount === 0) {
			const tableCardBody = tbody.closest('.card-body');
			const placeholderCardBody = tableCardBody.parentElement.querySelector('.card-body.placeholder');
			tableCardBody.classList.add('d-none');
			placeholderCardBody.classList.remove('d-none');
		}
	}
}
