import { applyLoading, sendPostRequest, Toast } from './shared.js';

const toggleKioskBtn = document.getElementById('toggleKiosk');
const devKioskStatus = document.getElementById('devKioskStatus');

toggleKioskBtn.addEventListener('click', async () => {
	let isKiosk = JSON.parse(toggleKioskBtn.getAttribute('data-kiosk'));
	applyLoading(toggleKioskBtn, `${isKiosk ? 'Deauthorizing' : 'Authorizing'} Kiosk...`, true);

	try {
		const data = await sendPostRequest(isKiosk ? kioskDeauthorizePostUrl : kioskAuthorizePostUrl);
		isKiosk = Boolean(data?.kiosk);
		toggleKioskBtn.setAttribute('data-kiosk', isKiosk);
		Toast.fire({
			text: isKiosk ? 'Kiosk authorized.' : 'Kiosk deauthorized.',
			icon: 'success',
		});
	} catch (err) {
		console.error(err);
	} finally {
		toggleKioskBtn.classList.toggle('btn-danger', isKiosk);
		toggleKioskBtn.classList.toggle('btn-warning', !isKiosk);
		toggleKioskBtn.textContent = `${isKiosk ? 'Deauthorize' : 'Authorize'} Kiosk`;
		toggleKioskBtn.disabled = false;

		if (devKioskStatus) {
			devKioskStatus.textContent = `Kiosk: ${isKiosk ? 'Authorized' : 'Unauthorized'}`;
			devKioskStatus.classList.toggle('text-bg-danger', !isKiosk);
			devKioskStatus.classList.toggle('text-bg-success', isKiosk);
		}
	}
});
