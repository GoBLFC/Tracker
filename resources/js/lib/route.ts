import { inject } from 'vue';
import type { InjectionKey } from 'vue';
import type { route as routeFn } from 'vendor/tightenco/ziggy';

/**
 * Ziggy route() function type
 */
export type ZiggyFn = typeof routeFn;

/**
 * Route parameter type for passing through to Ziggy
 */
export type Route = RouteName | ZiggyParams;

/**
 * Parameters for a Ziggy route call
 */
export type ZiggyParams = Exclude<GetFunctionOverloads<ZiggyFn>[number]['parameters'], [] | [undefined, ...unknown[]]>;

/**
 * Type for a Ziggy route name
 */
export type RouteName = Exclude<GetFunctionOverloads<ZiggyFn>[number]['parameters'][0], undefined>;

/**
 * Key to use for injecting the Ziggy route helper
 */
export const injectKey = Symbol() as InjectionKey<ZiggyFn>;

/**
 * Injects the Ziggy route helper provided from the global function
 */
export function useRoute() {
	return inject(injectKey)!;
}

/**
 * Resolves a route parameter to a URL using a given Ziggy function
 */
export function resolveRoute(route: Route, routeFn: ZiggyFn): string {
	// @ts-expect-error: TypeScript can't handle resolving overloads from tuple type unions
	return Array.isArray(route) ? routeFn(...route) : routeFn(route);
}

// The following is an awful hack to get TypeScript to be able to recognize multiple overloaded function signatures
// since the built-in Parameters type only gets the signature of the last overload in the source code.
// This is taken from a StackOverflow answer: https://stackoverflow.com/a/79299137
interface FunctionSignature {
	parameters: AnyArray;
	returnType: unknown;
}

// May look like a no-op but in actuality strips out everything except properties.
// This means it removes function signatures, constructor signatures, etc.
type ToObject<T extends object> = {
	[K in keyof T]: T[K];
};

// @ts-expect-error - This is a core type that unfortunately intrinsically has an error.
// The issue with a type like this is in theory they could overlap and the resulting type
// could become something like `never` or something ill-behaved.
// Fortunately this is used to add an overload which can never really conflict
// and so is pretty safe.
interface AddSignature<T extends object, Params extends AnyArray, Return> extends T {
	//    ^ Interface 'AddSignature<T, Params, Return>' incorrectly extends interface 'T'.
	//        'AddSignature<T, Params, Return>' is assignable to the constraint of type 'T', but 'T' could be instantiated with a different subtype of constraint 'object'.
	// The above is the suppressed error

	(...args: Params): Return;
}

// Returns a `FunctionSignature[]`.
type GetFunctionOverloads<
	T extends AnyFunction,
	Shape extends object = ToObject<T>,
	// This would be the first part to change if you want to change the return type.
	Signatures extends FunctionSignature[] = [],
> = Shape extends T
	? Signatures
	: T extends AddSignature<Shape, infer Params, infer Return>
		? GetFunctionOverloads<
				T,
				AddSignature<Shape, Params, Return>,
				// This would be the second.
				[{ parameters: Params; returnType: Return }, ...Signatures]
			>
		: Signatures;

// This type is written this way intentionally.
// It does truly allow any function to be assigned to it.
// However `...args: any[]` can be very type unsafe, this is much safer.
type AnyFunction = (arg0: never, ...args: never[]) => unknown;

type AnyArray = readonly unknown[];
