import { ref } from 'vue';

const VALIDATOR_BASE_URL = 'http://localhost:7071';

const HEALTH_POLL_MS = 5000;
const STATUS_POLL_MS = 1500;

export type NfcTapStage = 'idle' | 'searching' | 'waiting' | 'processing' | 'error';

export interface NfcTap {
	uid: string;
	attendeeId: number;
	conventionId?: number;
	scannedAt?: string;
}

interface HealthResponse {
	alive: boolean;
	readerReady: boolean;
}

interface StatusResponse {
	valid: boolean;
	uid?: string;
	attendeeId?: number;
	conventionId?: number;
	scannedAt?: string;
}

/**
 * Polls a workstation-local ConcatNFCValidator instance for a validated badge tap and invokes onTap for
 * each one. onTap is awaited - the stage is 'processing' while it's pending, and if it throws, the stage becomes
 * 'error' with the message exposed via `error` until the next tap comes in. The scan is acknowledged (so the
 * validator clears it and accepts the next one) regardless of whether onTap succeeds.
 */
export function useNfcTap(onTap: (tap: NfcTap) => void | Promise<void>) {
	const stage = ref<NfcTapStage>('idle');
	const error = ref<string | null>(null);

	let generation = 0;

	/**
	 * Begins polling the validator. Safe to call again to restart after stop().
	 */
	function start() {
		if (stage.value !== 'idle' && stage.value !== 'error') return;
		error.value = null;
		stage.value = 'searching';
		const myGeneration = ++generation;
		void pollHealth(myGeneration);
	}

	/**
	 * Stops polling. Any in-flight request's result will be ignored once it resolves.
	 */
	function stop() {
		generation++;
		stage.value = 'idle';
	}

	/**
	 * Polls /health until the validator answers, then switches to polling /status
	 */
	async function pollHealth(myGeneration: number) {
		if (myGeneration !== generation) return;

		try {
			const res = await fetch(`${VALIDATOR_BASE_URL}/health`, { signal: AbortSignal.timeout(3000) });
			if (!res.ok) throw new Error(`Unexpected status ${res.status}`);
			await (res.json() as Promise<HealthResponse>);

			if (myGeneration !== generation) return;
			stage.value = 'waiting';
			void pollStatus(myGeneration);
			return;
		} catch {
			// Validator not reachable - fall through to marking it as searching and retrying below
		}

		if (myGeneration !== generation) return;
		stage.value = 'searching';
		setTimeout(() => void pollHealth(myGeneration), HEALTH_POLL_MS);
	}

	/**
	 * Polls /status for a validated badge tap. Falls back to pollHealth if the validator stops responding.
	 */
	async function pollStatus(myGeneration: number) {
		if (myGeneration !== generation) return;

		let status: StatusResponse;
		try {
			const res = await fetch(`${VALIDATOR_BASE_URL}/status`, { signal: AbortSignal.timeout(3000) });
			if (!res.ok) throw new Error(`Unexpected status ${res.status}`);
			status = await res.json();
		} catch {
			if (myGeneration !== generation) return;
			// A single missed poll doesn't necessarily mean the validator is down - recheck health right away
			// instead of dropping to 'searching' (and hiding the tap-in UI) over one transient blip.
			void pollHealth(myGeneration);
			return;
		}

		if (myGeneration !== generation) return;

		if (!status.valid || status.attendeeId == null || !status.uid) {
			setTimeout(() => void pollStatus(myGeneration), STATUS_POLL_MS);
			return;
		}

		const tap: NfcTap = {
			uid: status.uid,
			attendeeId: status.attendeeId,
			conventionId: status.conventionId,
			scannedAt: status.scannedAt,
		};

		error.value = null;
		stage.value = 'processing';

		// Acknowledge the scan so the validator can clear it and accept the next one, regardless of whether
		// onTap below succeeds.
		fetch(`${VALIDATOR_BASE_URL}/ack`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ uid: tap.uid }),
		}).catch(() => {
			// Non-fatal - the validator will expire the pending scan on its own after a while
		});

		try {
			await onTap(tap);
			if (myGeneration !== generation) return;
			stage.value = 'waiting';
		} catch (err) {
			if (myGeneration !== generation) return;
			error.value = err instanceof Error ? err.message : 'Failed to handle badge tap.';
			stage.value = 'error';
		}

		if (myGeneration !== generation) return;
		setTimeout(() => void pollStatus(myGeneration), STATUS_POLL_MS);
	}

	return { stage, error, start, stop };
}
