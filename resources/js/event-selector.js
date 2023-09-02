document.addEventListener('DOMContentLoaded', () => {
	const selector = document.getElementById('eventSelector');
	selector.addEventListener('change', () => {
		window.location = selector.getAttribute('data-route').replace(/event-id/ig, selector.value);
	});
});
