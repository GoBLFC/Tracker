<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
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
	@vite('resources/js/app.js')
	@inertiaHead
</head>
<body>
	<noscript>
		<div class="d-flex flex-column justify-content-center align-items-center mt-5 mb-0">
			<div class="alert alert-warning">
				Tracker requires JavaScript to work properly.
			</div>
		</div>
	</noscript>

	@inertia
</body>
</html>
