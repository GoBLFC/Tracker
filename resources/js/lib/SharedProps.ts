import type { PageProps } from '@inertiajs/core';
import type User from '../data/User';
import type Event from '../data/Event';
import type { Flashes } from './toast';

/**
 * Inertia shared data properties
 */
export default interface SharedProps extends PageProps {
	auth: {
		user: User | null;
	};
	activeEvent: Event | null;
	timezone: string;
	kioskLifetime: number;
	isGatekeeper: boolean;
	isDevMode: boolean;
	isKiosk: boolean;
	isDebug: boolean;
	flash: Flashes;
}
