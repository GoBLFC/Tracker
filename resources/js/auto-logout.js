import { clockDuration } from './shared.js';

document.addEventListener('DOMContentLoaded', () => {
	const logoutBtn = document.getElementById('logout');

	// Start the logout timer
	if(logoutTime < Infinity) {
		const logout = new Date(Date.now() + logoutTime * 1000 + 500);
		renderLogout(logout);
		setTimeout(() => { window.location.href = logoutUrl; }, logoutTime * 1000 + 500);
		setInterval(() => { renderLogout(logout); }, 1000);
	}

	function renderLogout(logout) {
		const timeDiff = logout.getTime() - Date.now();
		logoutBtn.textContent = `Logout (${timeDiff > 1000 ? clockDuration(timeDiff) : 'Goodbye!'})`;
	}
});
