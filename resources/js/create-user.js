import { sendPostRequest, Toast } from './shared';

document.addEventListener('DOMContentLoaded', () => {
	const createBtn = document.getElementById('createUser');
	if(!createBtn) return;
	const badgeIdIpt = createBtn.parentElement.querySelector('input[type="text"]');

	createBtn.disabled = true;
	badgeIdIpt.addEventListener('input', () => {
		createBtn.disabled = !badgeIdIpt.value?.trim();
	});

	createBtn.addEventListener('click', async () => {
		const badgeId = badgeIdIpt.value?.trim();
		if(!badgeId) return;
		await createUser(badgeId);
		badgeIdIpt.value = '';
	});
});

export async function createUser(badge_id) {
	const data = await sendPostRequest(userStoreUrl, { badge_id });
	Toast.fire({
		text: `User (${data.user.badge_id}) created.`,
		icon: "success"
	});
}
