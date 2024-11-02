import '../sass/app.scss';
import 'bootstrap/js/dist/modal';
import.meta.glob(['../img/**']);

import { createApp, type DefineComponent, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import Aura from '@primevue/themes/aura';
import type { route as routeFn } from '../../vendor/tightenco/ziggy/src/js';
import { injectKey as routeInjectKey } from './lib/route';

import BaseLayout from './Layouts/BaseLayout.vue';
import MainLayout from './Layouts/MainLayout.vue';

const appName = document.getElementsByTagName('title')[0]?.innerText;

createInertiaApp({
	title: (title) => (title ? `${title} | ${appName}` : appName),

	async resolve(name) {
		const pages = import.meta.glob<Promise<DefineComponent>>('./Pages/**/*.vue', { eager: true });
		const page = await resolvePageComponent<DefineComponent>(`./Pages/${name}.vue`, pages);
		page.default.layout ??= [BaseLayout, MainLayout];
		return page;
	},

	setup({ el, App, props, plugin }) {
		const app = createApp({ render: () => h(App, props) })
			.use(plugin)
			.use(PrimeVue, {
				theme: {
					preset: Aura,
				},
			})
			.use(ToastService)
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
