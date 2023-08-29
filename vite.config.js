import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
	plugins: [
		laravel({
			input: [
				'resources/sass/app.scss',
				'resources/js/app.js',
				'resources/js/login.js',
				'resources/js/time.js',
				'resources/js/manage.js',
				'resources/js/auto-logout.js',
				'resources/js/toggle-kiosk.js',
				'resources/js/seamless-forms.js',
				'resources/js/admin/users.js',
			],
			refresh: true,
		}),
	],

	server: {
		hmr: {
			host: 'localhost',
		},
	},
});
