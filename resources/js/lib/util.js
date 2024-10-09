/**
 * Checks whether an element is in the browser's viewport
 * @param {HTMLElement} elem
 * @param {boolean} [partial=false] Whether to allow the element being partially in view
 * @returns
 */
export function isElementInView(elem, partial = false) {
	const rect = elem.getBoundingClientRect();
	if (partial) return rect.top < window.innerHeight && rect.bottom > 0;
	return rect.top >= 0 && rect.bottom <= window.innerHeight;
}
