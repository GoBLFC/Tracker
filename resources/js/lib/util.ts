/**
 * Checks whether an element is in the browser's viewport
 * @param partial Whether to allow the element being partially in view
 */
export function isElementInView(elem: HTMLElement, partial = false): boolean {
	const rect = elem.getBoundingClientRect();
	if (partial) return rect.top < window.innerHeight && rect.bottom > 0;
	return rect.top >= 0 && rect.bottom <= window.innerHeight;
}
