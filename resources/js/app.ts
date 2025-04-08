import '../css/app.css';
import.meta.glob(['../img/**']);

import { createApp, type DefineComponent, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import Tooltip from 'primevue/tooltip';
import type { route as routeFn } from 'vendor/tightenco/ziggy/src/js';
import { appNameInjectKey } from './lib/settings';
import { injectKey as routeInjectKey } from './lib/route';
import Theme from './theme';

import BaseLayout from './Layouts/BaseLayout.vue';
import NavLayout from './Layouts/NavLayout.vue';

const appName = document.getElementsByTagName('title')[0]?.innerText;

createInertiaApp({
	title: (title) => (title ? `${title} | ${appName}` : appName),

	async resolve(name) {
		const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue');
		const page = await resolvePageComponent<DefineComponent>(`./Pages/${name}.vue`, pages);
		page.default.layout ??= [BaseLayout, NavLayout];
		return page;
	},

	setup({ el, App, props, plugin }) {
		const app = createApp({ render: () => h(App, props) })
			.use(plugin)
			.use(PrimeVue, {
				theme: {
					preset: Theme,
					options: {
						darkModeSelector: '.dark',
						cssLayer: {
							name: 'primevue',
							order: 'theme, base, primevue, components, utilities',
						},
					},
				},
			})
			.use(ToastService)
			.use(ConfirmationService)
			.directive('tooltip', Tooltip)
			.provide(appNameInjectKey, appName)
			.provide(routeInjectKey, route);

		app.config.globalProperties.$appName = appName;
		app.mount(el);
	},
});

declare global {
	var route: typeof routeFn;
}

declare module 'vue' {
	interface ComponentCustomProperties {
		$appName: string;
	}
}
