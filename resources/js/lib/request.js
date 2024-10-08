import { ref, inject } from 'vue';
import axios from 'axios';

/**
 * Composable wrapper for making a single HTTP request at a time
 */
export function useRequest() {
	const resolveRoute = inject('route');
	const processing = ref(false);
	const error = ref(null);

	/**
	 * Sends a request
	 * @param {'GET'|'POST'|'PUT'|'PATCH'|'DELETE'} method HTTP method to use for the request
	 * @param {string|Array} route Name of the route to send the request to, or an array of the name and parameters
	 * @param {*} [data] Data to send as the request body (or as query params for GET requests)
	 * @returns {Promise<*>} Response data
	 */
	async function send(method, route, data) {
		processing.value = true;
		error.value = null;

		const url = Array.isArray(route) ? resolveRoute(...route) : resolveRoute(route);

		try {
			return (
				await axios({
					method,
					url,
					data: method !== 'GET' ? data : undefined,
					params: method === 'GET' ? data : undefined,
					timeout: 10000,
				})
			).data;
		} catch (err) {
			console.error(`Error sending ${method} request to ${route} (${url}):`, err, data);
			error.value = err;
			throw err;
		} finally {
			processing.value = false;
		}
	}

	/**
	 * Sends a GET request
	 * @param {string} route Name of the route to send the request to, or an array of the name and parameters
	 * @returns {Promise<*>} Response data
	 */
	async function get(route, params) {
		return await send('GET', route, params);
	}

	/**
	 * Sends a POST request
	 * @param {string} route Name of the route to send the request to, or an array of the name and parameters
	 * @param {*} [data] Data to send as the request body
	 * @returns {Promise<*>} Response data
	 */
	async function post(route, data) {
		return await send('POST', route, data);
	}

	/**
	 * Sends a PUT request
	 * @param {string} route Name of the route to send the request to, or an array of the name and parameters
	 * @param {*} [data] Data to send as the request body
	 * @returns {Promise<*>} Response data
	 */
	async function put(route, data) {
		return await send('PUT', route, data);
	}

	/**
	 * Sends a DELETE request
	 * @param {string} route Name of the route to send the request to, or an array of the name and parameters
	 * @param {*} [data] Data to send as the request body
	 * @returns {Promise<*>} Response data
	 */
	async function del(route, data) {
		return await send('DELETE', route, data);
	}

	return {
		processing,
		error,
		send,
		get,
		post,
		put,
		del,
	};
}
