import { toRef } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Gets information about the authenticated user from the Inertia page properties
 */
export function useUser() {
	const page = usePage();

	return {
		id: toRef(() => page.props.auth.user?.id),
		badgeId: toRef(() => page.props.auth.user?.badge_id),
		username: toRef(() => page.props.auth.user?.username),
		badgeName: toRef(() => page.props.auth.user?.badge_name),
		displayName: toRef(() => page.props.auth.user?.badge_name ?? page.props.auth.user?.username),
		role: toRef(() => page.props.auth.user?.role),
		roleName: toRef(() => (page.props.auth.user?.role ? roleNames[page.props.auth.user?.role] : null)),
		isLoggedIn: toRef(() => Boolean(page.props.auth.user)),
	};
}

/**
 * Role names mapped by their numeric ID
 */
export const roleNames = {
	3: 'Admin',
	2: 'Manager',
	1: 'Lead',
	0: 'Volunteer',
	'-1': 'Attendee',
	'-2': 'Banned',
};
