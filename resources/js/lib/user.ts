import { toRef } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type SharedProps from '../data/SharedProps';

/**
 * Provides information about the authenticated user from the Inertia page properties
 */
export function useUser() {
	const page = usePage<SharedProps>();

	return {
		id: toRef(() => page.props.auth.user?.id),
		badgeId: toRef(() => page.props.auth.user?.badge_id),
		username: toRef(() => page.props.auth.user?.username),
		badgeName: toRef(() => page.props.auth.user?.badge_name),
		displayName: toRef(() => page.props.auth.user?.badge_name ?? page.props.auth.user?.username),
		role: toRef(() => page.props.auth.user?.role),
		roleName: toRef(() => (page.props.auth.user?.role ? roleNames[page.props.auth.user?.role] : null)),

		isLoggedIn: toRef(() => Boolean(page.props.auth.user)),
		isGatekeeper: toRef(() => page.props.isGatekeeper),
		isBanned: toRef(() => page.props.auth.user?.role === -2),
		isStaff: toRef(() => page.props.auth.user?.role ?? 0 >= 1),
		isLead: toRef(() => page.props.auth.user?.role ?? 0 >= 2),
		isManager: toRef(() => page.props.auth.user?.role ?? 0 >= 3),
		isAdmin: toRef(() => page.props.auth.user?.role === 4),
	};
}

/**
 * Role names mapped by their numeric ID
 */
export const roleNames = {
	4: 'Admin',
	3: 'Manager',
	2: 'Lead',
	1: 'Staff',
	0: 'Volunteer',
	'-1': 'Attendee',
	'-2': 'Banned',
};
