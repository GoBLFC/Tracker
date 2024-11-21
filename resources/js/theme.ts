import { definePreset } from '@primevue/themes';
import Aura from '@primevue/themes/aura';

export default definePreset(Aura, {
	semantic: {
		primary: {
			50: '{indigo.50}',
			100: '{indigo.100}',
			200: '{indigo.200}',
			300: '{indigo.300}',
			400: '{indigo.400}',
			500: '{indigo.500}',
			600: '{indigo.600}',
			700: '{indigo.700}',
			800: '{indigo.800}',
			900: '{indigo.900}',
			950: '{indigo.950}',
		},
		colorScheme: {
			light: {
				primary: {
					color: '{primary.500}',
					contrastColor: '#ffffff',
					hoverColor: '{primary.600}',
					activeColor: '{primary.700}',
				},
				highlight: {
					background: '{primary.50}',
					focusBackground: '{primary.100}',
					color: '{primary.700}',
					focusColor: '{primary.800}',
				},
				surface: {
					0: '#ffffff',
					50: '{gray.50}',
					100: '{gray.100}',
					200: '{gray.200}',
					300: '{gray.300}',
					400: '{gray.400}',
					500: '{gray.500}',
					600: '{gray.600}',
					700: '{gray.700}',
					800: '{gray.800}',
					900: '{gray.900}',
					950: '{gray.950}',
				},
			},
			dark: {
				primary: {
					color: '{primary.400}',
					contrastColor: '{surface.900}',
					hoverColor: '{primary.300}',
					activeColor: '{primary.200}',
				},
				highlight: {
					background: 'color-mix(in srgb, {primary.400}, transparent 84%)',
					focusBackground: 'color-mix(in srgb, {primary.400}, transparent 76%)',
					color: 'rgba(255,255,255,.87)',
					focusColor: 'rgba(255,255,255,.87)',
				},
				surface: {
					0: '#ffffff',
					50: '{gray.50}',
					100: '{gray.100}',
					200: '{gray.200}',
					300: '{gray.300}',
					400: '{gray.400}',
					500: '{gray.500}',
					600: '{gray.600}',
					700: '{gray.700}',
					800: '{gray.800}',
					900: '{gray.900}',
					950: '{gray.950}',
				},
			},
		},
	},
});
