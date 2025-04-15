import { ref, type Ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import type { AxiosError, AxiosResponse, Method } from 'axios';
import { resolveRoute, useRoute, type Route } from './route';
import { useToast } from './toast';
import { reset as resetLogoutTimers } from './logout';

/**
 * Composable wrapper for making a single HTTP request at a time and tracking loading/error state
 */
export function useRequest() {
	const toast = useToast();

	const routeFn = useRoute();
	const processing = ref(false);
	const error = ref<AxiosError | null>(null);

	/**
	 * Sends a request
	 * @param method HTTP method to use for the request
	 * @param route Name of the route to send the request to, or an array of parameters for Ziggy
	 * @param data Data to send as the request body (or as query params for GET requests)
	 * @returns Response data
	 */
	async function send<T, D = unknown>(method: Method, route: Route, data?: D): Promise<T> {
		processing.value = true;
		error.value = null;

		const url = resolveRoute(route, routeFn);

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
	 * @param route Name of the route to send the request to, or an array of parameters for Ziggy
	 * @param params Query string parameters to send
	 * @returns Response data
	 */
	async function get<T, D = unknown>(route: Route, params?: D): Promise<T> {
		return await send('GET', route, params);
	}

	/**
	 * Sends a POST request
	 * @param route Name of the route to send the request to, or an array of parameters for Ziggy
	 * @param data Data to send as the request body
	 * @returns Response data
	 */
	async function post<T, D = unknown>(route: Route, data?: D): Promise<T> {
		return await send('POST', route, data);
	}

	/**
	 * Sends a PUT request
	 * @param route Name of the route to send the request to, or an array of parameters for Ziggy
	 * @param data Data to send as the request body
	 * @returns Response data
	 */
	async function put<T, D = unknown>(route: Route, data?: D): Promise<T> {
		return await send('PUT', route, data);
	}

	/**
	 * Sends a DELETE request
	 * @param route Name of the route to send the request to, or an array of parameters for Ziggy
	 * @param data Data to send as the request body
	 * @returns Response data
	 */
	async function del<T, D = unknown>(route: Route, data?: D): Promise<T> {
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

type InertiaData = Parameters<(typeof router)['get']>[1];
type InertiaOptions = Parameters<(typeof router)['get']>[2];

/**
 * Composable wrapper for sending a request with Inertia and tracking loading state
 */
export function useInertiaRequest() {
	const routeFn = useRoute();
	const processing = ref(false);

	/**
	 * Sends a GET Inertia request
	 * @param route Name of the route to send the request to, or an array of parameters for Ziggy
	 * @param data Data to send as query string parameters
	 * @param options Inertia visit options
	 */
	function get(route: Route, data?: InertiaData, options?: InertiaOptions) {
		const url = resolveRoute(route, routeFn);
		router.get(url, data, addProcessingToInertiaOptions(processing, options));
	}

	/**
	 * Sends a POST Inertia request
	 * @param route Name of the route to send the request to, or an array of parameters for Ziggy
	 * @param data Data to send as the request body
	 * @param options Inertia visit options
	 */
	function post(route: Route, data?: InertiaData, options?: InertiaOptions) {
		const url = resolveRoute(route, routeFn);
		router.post(url, data, addProcessingToInertiaOptions(processing, options));
	}

	/**
	 * Sends a PUT Inertia request
	 * @param route Name of the route to send the request to, or an array of parameters for Ziggy
	 * @param data Data to send as the request body
	 * @param options Inertia visit options
	 */
	function put(route: Route, data?: InertiaData, options?: InertiaOptions) {
		const url = resolveRoute(route, routeFn);
		router.put(url, data, addProcessingToInertiaOptions(processing, options));
	}

	/**
	 * Sends a PATCH Inertia request
	 * @param route Name of the route to send the request to, or an array of parameters for Ziggy
	 * @param data Data to send as the request body
	 * @param options Inertia visit options
	 */
	function patch(route: Route, data?: InertiaData, options?: InertiaOptions) {
		const url = resolveRoute(route, routeFn);
		router.patch(url, data, addProcessingToInertiaOptions(processing, options));
	}

	/**
	 * Sends a DELETE Inertia request
	 * @param route Name of the route to send the request to, or an array of parameters for Ziggy
	 * @param options Inertia visit options
	 */
	function del(route: Route, options?: InertiaOptions) {
		const url = resolveRoute(route, routeFn);
		router.delete(url, addProcessingToInertiaOptions(processing, options));
	}

	return {
		processing,
		get,
		post,
		put,
		patch,
		del,
	};
}

/**
 * Extends a set of inertia options with onStart and onFinish methods to change the state of a processing boolean ref
 */
function addProcessingToInertiaOptions(processing: Ref<boolean, boolean>, options: InertiaOptions = {}) {
	const extended: InertiaOptions = {
		preserveState: true,
		preserveScroll: true,
		...options,

		onStart(...args) {
			processing.value = true;
			if (options.onStart) options.onStart(...args);
		},
		onFinish(...args) {
			processing.value = false;
			if (options.onFinish) options.onFinish(...args);
		},
	};

	return extended;
}
