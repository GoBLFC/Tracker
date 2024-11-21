import { onMounted, onUnmounted, ref } from 'vue';

export type Theme = 'light' | 'dark';

/**
 * Composable wrapper for the browser's preferred theme
 */
export function useTheme() {
	const theme = ref<Theme>(getTheme());
	let matcher: MediaQueryList | null = null;

	// Start listening for theme changes
	onMounted(() => {
		matcher = window.matchMedia('(prefers-color-scheme: dark)');
		matcher.addEventListener('change', onThemeChange);
	});

	// Stop listening for theme changes
	onUnmounted(() => {
		matcher!.removeEventListener('change', onThemeChange);
		matcher = null;
	});

	/**
	 * Gets the current theme
	 */
	function getTheme(): Theme {
		if (window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark';
		return 'light';
	}

	/**
	 * Updates the current theme
	 */
	function onThemeChange() {
		theme.value = getTheme();
	}

	return { theme, getTheme };
}

export type Breakpoint = keyof typeof BREAKPOINTS;
export const BREAKPOINTS = {
	xs: 0,
	sm: 640,
	md: 768,
	lg: 1024,
	xl: 1280,
	'2xl': 1536,
};
const breakpointOrder: Breakpoint[] = ['2xl', 'xl', 'lg', 'md', 'sm', 'xs'];

/**
 * Composable wrapper for breakpoint handling
 */
export function useBreakpoint() {
	const breakpoint = ref<Breakpoint>(getBreakpoint());
	let matchers: MediaQueryList[] | null = null;

	// Start listening for breakpoint changes with a MediaQueryList per breakpoint. We do this rather than listen to the
	// window resize event so that events are only fired when the breakpoint is actually changing. Efficiency!
	onMounted(() => {
		matchers = [];

		for (const width of Object.values(BREAKPOINTS)) {
			const matcher = window.matchMedia(`(min-width: ${width}px)`);
			matcher.addEventListener('change', onBreakpointChange);
			matchers.push(matcher);
		}
	});

	// Stop listening for breakpoint changes
	onUnmounted(() => {
		for (const matcher of matchers!) {
			matcher.removeEventListener('change', onBreakpointChange);
		}

		matchers = null;
	});

	/**
	 * Gets the applicable breakpoint for a given display width
	 * @param [width] Display width, in pixels (defaults to the current viewport width)
	 */
	function getBreakpoint(width: number = window.innerWidth): Breakpoint {
		for (const bp of breakpointOrder) {
			if (width >= BREAKPOINTS[bp]) return bp;
		}

		return 'xs';
	}

	/**
	 * Updates the current breakpoint
	 */
	function onBreakpointChange() {
		breakpoint.value = getBreakpoint();
	}

	return { breakpoint, getBreakpoint };
}
