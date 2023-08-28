<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>{{ config('app.name') }}</title>

	<link rel="icon" sizes="32x32" href="{!! Vite::asset('resources/img/blfc-chip-32.png') !!}" />
	<link rel="icon" sizes="64x64" href="{!! Vite::asset('resources/img/blfc-chip-64.png') !!}" />
	<link rel="icon" sizes="128x128" href="{!! Vite::asset('resources/img/blfc-chip-128.png') !!}" />
	<link rel="icon" sizes="180x180" href="{!! Vite::asset('resources/img/blfc-chip-180.png') !!}" />
	<link rel="icon" sizes="192x192" href="{!! Vite::asset('resources/img/blfc-chip-192.png') !!}" />

	@prepend('styles')
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		@vite('resources/sass/app.scss')
	@endprepend
	@stack('styles')

	@prepend('modules')
		@vite('resources/js/app.js')
		@auth
			@vite('resources/js/auto-logout.js')
		@endauth
	@endprepend
	@stack('modules')
</head>
<body>
	<div class="container my-5">
		<header class="mb-4 text-center">
			<a href="{!! route('tracker.index') !!}" title="{{ config('app.name') }}">
				<img src="{!! Vite::asset('resources/img/blfc-chip.png') !!}" width="128" height="146" alt="BLFC Poker Chip Logo" />
			</a>
		</header>

		@devMode
			@include('partials.dev-badges')
		@enddevMode

		<main>
			@yield('content')
		</main>

		<footer class="@yield('footer-class')">
			<div class="@yield('footer-nav-class')">
				@section('footer-nav')
					@auth
						<div class="autologout float-end mb-3">
							<a id="logout" class="btn btn-danger btn-sm" role="button" href="{!! route('auth.logout') !!}">Logout</a>
						</div>
					@endauth

					<button class="btn btn-sm btn-light float-end @auth me-2 @endauth" data-bs-toggle="modal" data-bs-target="#aboutModal">About</button>
				@show
			</div>
		</footer>
	</div>

	@prepend('modals')
		@include('partials.about-modal')
	@endprepend
	@stack('modals')

	@prepend('scripts')
		<script type="text/javascript">
			const _token = '{!! csrf_token() !!}';
			@auth
				const logoutUrl = '{!! route('auth.logout') !!}';
				const logoutTime = @devMode 3600 @else 60 @enddevMode;
			@endauth
		</script>
	@endprepend
	@stack('scripts')
</body>
</html>
