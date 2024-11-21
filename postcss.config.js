/** @type {import('postcss-load-config').Config} */
export default {
	plugins: {
		tailwindcss: {},
		autoprefixer: {},
		...(process.env.NODE_ENV === 'production' ? { cssnano: {} } : {}),
	},
};
