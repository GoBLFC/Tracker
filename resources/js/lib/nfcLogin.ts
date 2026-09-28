import { useInertiaRequest } from './request';
import { useNfcTap, type NfcTap } from './nfcTap';

/**
 * Logs in the Tracker user matching a badge tapped on a workstation-local ConcatNFCValidator instance. Only meaningful on
 * sessions authorized as a Kiosk (callers should gate on that separately - this composable has no opinion on it
 * beyond that the backend login endpoint enforces it too).
 */
export function useNfcLogin() {
	const inertiaRequest = useInertiaRequest();

	return useNfcTap(
		(tap: NfcTap) =>
			new Promise<void>((resolve, reject) => {
				inertiaRequest.post(
					'auth.nfc.post',
					{ attendeeId: tap.attendeeId },
					{
						onSuccess: () => resolve(),
						onError(errors) {
							reject(new Error(errors.attendeeId ?? Object.values(errors)[0] ?? 'NFC login failed.'));
						},
					},
				);
			}),
	);
}
