import TailwindPrimeUi from 'tailwindcss-primeui';

/** @type {import('tailwindcss').Config} */
export default {
	content: ['./resources/js/**/*.vue', './resources/views/**/*.blade.php'],
	plugins: [TailwindPrimeUi],
	safelist: ['sm:not-sr-only', 'md:not-sr-only', 'lg:not-sr-only', 'xl:not-sr-only'],
};
