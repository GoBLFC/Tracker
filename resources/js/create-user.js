import { applyLoading, sendPostRequest, Toast } from './shared';

document.addEventListener('DOMContentLoaded', () => {
	const createBtn = document.getElementById('createUser');
	if(!createBtn) return;
	const badgeIdIpt = createBtn.parentElement.querySelector('input[type="text"]');

	updateCreateBtn();
	badgeIdIpt.addEventListener('input', updateCreateBtn);

	createBtn.addEventListener('click', async () => {
		const badgeId = badgeIdIpt.value?.trim();
		if(!badgeId) return;

		applyLoading(createBtn, 'Creating user...');
		try {
			await createUser(badgeId);
		} finally {
			applyLoading(createBtn);
			setTimeout(updateCreateBtn, 0);
		}

		badgeIdIpt.value = '';
	});

	function updateCreateBtn() {
		createBtn.disabled = !badgeIdIpt.value?.trim();
	}
});

export async function createUser(badge_id) {
	const data = await sendPostRequest(userStoreUrl, { badge_id });
	Toast.fire({
		text: `User (#${data.user.badge_id}) created.`,
		icon: 'success',
	});
}
