import TailwindPrimeUi from 'tailwindcss-primeui';

/** @type {import('tailwindcss').Config} */
export default {
	content: ['./resources/js/**/*.vue', './resources/views/**/*.blade.php'],
	plugins: [TailwindPrimeUi],
};
