import $ from 'jquery';
import Swal from 'sweetalert2/dist/sweetalert2.js';
import { Tooltip } from 'bootstrap';
import { Duration } from 'luxon';

export const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    showClass: {
        popup: "animate__animated animate__slideInDown animate__faster"
    }
});

export function debounce(func, wait, immediate) {
    var timeout;
    return function () {
        var context = this, args = arguments;
        var later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
};

export function applyLoading(elem, text) {
    if (text) {
        $(elem).data('original-text', $(elem).html())
        	.html('<i class="fa fa-circle-notch fa-spin"></i> ' + text)
			.prop('disabled', true);
    } else {
		$(elem).html($(elem).data('original-text'))
			.prop('disabled', false);
	}
}

export function addRow(key, elem, data) {
    let innerTable = '';
    for (let i = 0; i < data.length; i++) {
        const t = (key && i === 0) ? 'th scope="row"' : 'td';
        innerTable += '<' + t + '>' + data[i] + '</' + t + '>';
    }
    $(elem).append('<tr>' + innerTable + '</tr>');
}

export function getButtonInput(object) {
    return $(object).parent().parent().find('.form-control').val();
}

export function getButtonSelect(object) {
    return $(object).parent().parent().find('.form-select').val();
}

export function getTableKey(object) {
    return $('th:first', $(object).parents('tr')).text();
}

/**
 * Make a request to a given URL and automatically decode the response from JSON when applicable
 * @param {string|URL} url
 * @param {Object} options
 * @returns {*}
 */
export async function sendRequest(url, options) {
	let response;
	try {
		// Make the request
		response = await fetch(url, {
			headers: {
				'Content-Type': 'application/json',
				Accept: 'application/json',
			},
			...options,
		});
	} catch(err) {
		// Display a generic error message in the case of a network error
		Toast.fire({
			text: 'Internal error, please contact a staff member for assistance.',
			icon: 'error',
		});
		throw err;
	}

	// Handle happy responses; assume any content is JSON
	if(response.status === 205) return null;
	if(response.ok) return response.json();

	// The response isn't 2xx at this point, so display the error
	let data;
	try {
		// Try decoding the response as JSON for expected errors
		data = await response.json();
		Toast.fire({
			text: data.error ?? data.message,
			icon: 'warning',
		});
	} catch(err) {
		// Nevermind, guess it isn't expected, just parse it as text
		data = await response.text();
	}

	console.error(`Server returned ${response.status} error:`, data);
	throw new ServerError(response.status, data.error ?? data.message ?? data);
}

/**
 * Make a GET request to a given URL and automatically decode the response from JSON when applicable
 * @param {string|URL} url
 * @param {Object} parameters
 * @returns {*}
 */
export async function sendGetRequest(url, params = {}) {
	// Build the URL with the query string for the request
	const urlQS = new URL(url);
	for(const [key, val] of Object.entries(params)) urlQS.searchParams.set(key, val);

	return sendRequest(urlQS, { method: 'GET' });
}

/**
 * Make a POST request to a given URL and automatically decode the response from JSON when applicable
 * @param {string|URL} url
 * @param {Object} body
 * @returns {*}
 */
export async function sendPostRequest(url, body = {}) {
	return sendRequest(url, {
		method: 'POST',
		body: JSON.stringify({ _token, ...body }),
	});
}

/**
 * Make a PUT request to a given URL and automatically decode the response from JSON when applicable
 * @param {string|URL} url
 * @param {Object} body
 * @returns {*}
 */
export async function sendPutRequest(url, body = {}) {
	return sendRequest(url, {
		method: 'PUT',
		body: JSON.stringify({ _token, ...body }),
	});
}

/**
 * Make a DELETE request to a given URL and automatically decode the response from JSON when applicable
 * @param {string|URL} url
 * @param {Object} body
 * @returns {*}
 */
export async function sendDeleteRequest(url, body = {}) {
	return sendRequest(url, {
		method: 'DELETE',
		body: JSON.stringify({ _token, ...body }),
	});
}

/**
 * Error for a response from the server
 */
export class ServerError extends Error {
	constructor(status, message) {
		super(message);
		this.name = 'ServerError';

		/**
		 * HTTP status code from the response
		 * @type {number}
		 */
		this.status = status;
	}
}

/**
 * Get a human-friendly representation of a duration of time (formatted like "6h 40m")
 * @param {number} timeMs
 * @returns {string}
 */
export function humanDuration(timeMs) {
	const duration = Duration.fromMillis(timeMs).shiftTo('hours', 'minutes');

	if(duration.hours > 0) {
		if(duration.minutes < 1) return duration.toFormat("h'h'");
		return duration.toFormat("h'h' m'm'");
	}

	return duration.toFormat("m'm'");
}

/**
 * Get a clock-like representation of a duration of time (formatted like "5:06:32")
 * @param {number} timeMs
 * @returns {string}
 */
export function clockDuration(timeMs) {
	const duration = Duration.fromMillis(timeMs).shiftTo('hours', 'minutes', 'seconds');
	if(duration.hours > 0) return duration.toFormat('h:mm:ss');
	return duration.toFormat('m:ss');
}

/**
 * Initializes any Bootstrap tooltips on the page
 */
export function initTooltips(elem = document) {
	const tooltipTriggerList = elem.querySelectorAll('[data-bs-toggle="tooltip"]');
	for(const tooltipEl of tooltipTriggerList) new Tooltip(tooltipEl);
}
