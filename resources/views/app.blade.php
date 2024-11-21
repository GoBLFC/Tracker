<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<link rel="icon" sizes="32x32" href="{!! Vite::asset('resources/img/blfc-chip-32.png') !!}" />
	<link rel="icon" sizes="64x64" href="{!! Vite::asset('resources/img/blfc-chip-64.png') !!}" />
	<link rel="icon" sizes="128x128" href="{!! Vite::asset('resources/img/blfc-chip-128.png') !!}" />
	<link rel="icon" sizes="180x180" href="{!! Vite::asset('resources/img/blfc-chip-180.png') !!}" />
	<link rel="icon" sizes="192x192" href="{!! Vite::asset('resources/img/blfc-chip-192.png') !!}" />

	<title inertia>{!! config('app.name') !!}</title>

	@routes
	@vite('resources/js/app.ts')
	@inertiaHead
</head>
<body>
	@inertia

	<noscript>
		<div class="flex flex-col justify-center items-center h-screen p-4">
			<p class="p-4 rounded-md text-xl text-center bg-surface-200 dark:bg-surface-700 border border-red-300 dark:border-red-700">
				Tracker requires JavaScript to work properly.
			</p>
		</div>
	</noscript>
</body>
</html>
