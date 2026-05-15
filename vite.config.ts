import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import Components from 'unplugin-vue-components/vite';
import { PrimeVueResolver } from '@primevue/auto-import-resolver';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
	plugins: [
		vue(),
		tailwindcss(),
		Components({
			resolvers: [PrimeVueResolver()],
		}),
		laravel({
			input: [
				'resources/js/app.ts',
				'resources/js/legacy/app.js',
				'resources/js/legacy/auto-logout.js',
				'resources/js/legacy/event-selector.js',
				'resources/js/legacy/admin/report.js',
			],
			refresh: true,
			assets: ['resources/img/**'],
		}),
	],

	resolve: {
		alias: {
			'@': fileURLToPath(new URL('./resources/js', import.meta.url)),
		},
	},

	server: {
		hmr: {
			host: 'localhost',
		},
	},
});
