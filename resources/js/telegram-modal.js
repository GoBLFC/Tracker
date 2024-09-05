import { Toast } from './shared';
import QRCode from 'qrcode';

document.addEventListener('DOMContentLoaded', () => {
	const modal = document.getElementById('telegramModal');
	const canvas = document.getElementById('telegramQrCanvas');

	modal.addEventListener(
		'show.bs.modal',
		async () => {
			try {
				await QRCode.toCanvas(canvas, tgSetupUrl, {
					errorCorrectionLevel: 'medium',
					margin: 2,
					width: canvas.width,
				});
			} catch (err) {
				console.error('Error generating Telegram QR code', err);
				Toast.fire({
					title: 'Failed to generate QR code',
					text: 'See the browser console for more information.',
					icon: 'error',
				});
			}
		},
		{ once: true },
	);
});
