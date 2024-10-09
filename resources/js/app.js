import '../sass/app.scss';
import 'bootstrap/js/dist/modal';
import.meta.glob(['../img/**']);

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import BaseLayout from './Layouts/BaseLayout.vue';
import MainLayout from './Layouts/MainLayout.vue';

const appName = window.document.getElementsByTagName('title')[0]?.innerText;

createInertiaApp({
	title: (title) => (title ? `${title} | ${appName}` : appName),

	async resolve(name) {
		const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
		const page = await resolvePageComponent(`./Pages/${name}.vue`, pages);
		page.default.layout ??= [BaseLayout, MainLayout];
		return page;
	},

	setup({ el, App, props, plugin }) {
		const app = createApp({ render: () => h(App, props) }).use(plugin);
		app.config.globalProperties.$appName = appName;
		app.provide('route', route).mount(el);
	},
});
