import { applyLoading, postAction, Toast } from './shared.js';

const toggleKioskBtn = document.getElementById('toggleKiosk');
const devKioskStatus = document.getElementById('devKioskStatus');
let isKiosk = JSON.parse(toggleKioskBtn.getAttribute('data-kiosk'));

toggleKioskBtn.addEventListener('click', async () => {
	applyLoading(toggleKioskBtn, `${isKiosk ? 'Deauthorizing' : 'Authorizing'} Kiosk...`, true);

	try {
		const response = await postAction(isKiosk ? kioskDeauthorizePostUrl : kioskAuthorizePostUrl);
		isKiosk = Boolean(response?.kiosk);
		Toast.fire({
			text: isKiosk ? 'Kiosk authorized.' : 'Kiosk deauthorized.',
			icon: 'success',
		});
	} catch(err) {
		console.error(err);
	} finally {
		toggleKioskBtn.classList.toggle('btn-danger', isKiosk);
		toggleKioskBtn.classList.toggle('btn-warning', !isKiosk);
		toggleKioskBtn.textContent = `${isKiosk ? 'Deauthorize' : 'Authorize'} Kiosk`;
		toggleKioskBtn.disabled = false;

		if(devKioskStatus) {
			devKioskStatus.textContent = `Kiosk: ${isKiosk ? 'Authorized' : 'Unauthorized'}`;
			devKioskStatus.classList.toggle('text-bg-danger', !isKiosk);
			devKioskStatus.classList.toggle('text-bg-success', isKiosk);
		}
	}
});
