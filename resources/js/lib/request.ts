import { ref } from 'vue';
import axios from 'axios';
import type { AxiosError, AxiosResponse, Method } from 'axios';
import { useRoute } from './route';
import { useToast } from './toast';
import { reset as resetLogoutTimers } from './logout';

/**
 * Composable wrapper for making a single HTTP request at a time
 */
export function useRequest() {
	const toast = useToast();

	const resolveRoute = useRoute();
	const processing = ref(false);
	const error = ref<AxiosError | null>(null);

	/**
	 * Sends a request
	 * @param method HTTP method to use for the request
	 * @param route Name of the route to send the request to, or an array of the name and parameters
	 * @param data Data to send as the request body (or as query params for GET requests)
	 * @returns Response data
	 */
	async function send<T, D = unknown>(
		method: Method,
		route: string | [string, string | string[]],
		data?: D,
	): Promise<T> {
		processing.value = true;
		error.value = null;

		// @ts-ignore: Type weirdness that requires way too much to fix
		const url = Array.isArray(route) ? resolveRoute(...route) : resolveRoute(route);

		try {
			return (
				await axios<T, AxiosResponse<T, D>, D>({
					method,
					url,
					data: method !== 'GET' ? data : undefined,
					params: method === 'GET' ? data : undefined,
					timeout: 10000,
				})
			).data;
		} catch (err) {
			console.error(`Error sending ${method} request to ${route} (${url}):`, err, data);

			if (!axios.isAxiosError(err)) throw err;
			error.value = err;

			if (err.response?.data?.errors) {
				const errors = Object.values(err.response.data.errors);
				const joined = errors.join('\n');
				if (errors.length > 1) toast.error('Validation errors', joined);
				else toast.error(joined);
			} else if (err.response?.data?.message || err.response?.data?.error) {
				toast.error(err.response.data.message ?? err.response.data.error);
			} else {
				toast.error('Error sending request', 'See the browser console for more information.');
			}

			throw err;
		} finally {
			processing.value = false;
			resetLogoutTimers();
		}
	}

	/**
	 * Sends a GET request
	 * @param route Name of the route to send the request to, or an array of the name and parameters
	 * @param params Query string parameters to send
	 * @returns Response data
	 */
	async function get<T, D = unknown>(route: string | [string, string | string[]], params?: D): Promise<T> {
		return await send('GET', route, params);
	}

	/**
	 * Sends a POST request
	 * @param route Name of the route to send the request to, or an array of the name and parameters
	 * @param data Data to send as the request body
	 * @returns Response data
	 */
	async function post<T, D = unknown>(route: string | [string, string | string[]], data?: D): Promise<T> {
		return await send('POST', route, data);
	}

	/**
	 * Sends a PUT request
	 * @param route Name of the route to send the request to, or an array of the name and parameters
	 * @param data Data to send as the request body
	 * @returns Response data
	 */
	async function put<T, D = unknown>(route: string | [string, string | string[]], data?: D): Promise<T> {
		return await send('PUT', route, data);
	}

	/**
	 * Sends a DELETE request
	 * @param route Name of the route to send the request to, or an array of the name and parameters
	 * @param data Data to send as the request body
	 * @returns Response data
	 */
	async function del<T, D = unknown>(route: string | [string, string | string[]], data?: D): Promise<T> {
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
