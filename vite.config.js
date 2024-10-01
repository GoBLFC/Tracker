import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
	plugins: [
		vue(),
		laravel({
			input: [
				'resources/sass/app.scss',
				'resources/js/app.js',
				'resources/js/legacy/app.js',
				'resources/js/legacy/login.js',
				'resources/js/legacy/time.js',
				'resources/js/legacy/manage.js',
				'resources/js/legacy/auto-logout.js',
				'resources/js/legacy/toggle-kiosk.js',
				'resources/js/legacy/create-user.js',
				'resources/js/legacy/event-selector.js',
				'resources/js/legacy/seamless-forms.js',
				'resources/js/legacy/attendee-log.js',
				'resources/js/legacy/admin/users.js',
				'resources/js/legacy/admin/departments.js',
				'resources/js/legacy/admin/events.js',
				'resources/js/legacy/admin/bonuses.js',
				'resources/js/legacy/admin/rewards.js',
				'resources/js/legacy/admin/report.js',
				'resources/js/legacy/admin/attendee-logs.js',
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
