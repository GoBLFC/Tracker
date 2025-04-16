<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>{{ config('app.name') }}</title>

	<link rel="icon" sizes="32x32" href="{!! Vite::asset('resources/img/event-logo-32.png') !!}" />
	<link rel="icon" sizes="64x64" href="{!! Vite::asset('resources/img/event-logo-64.png') !!}" />
	<link rel="icon" sizes="128x128" href="{!! Vite::asset('resources/img/event-logo-128.png') !!}" />
	<link rel="icon" sizes="180x180" href="{!! Vite::asset('resources/img/event-logo-180.png') !!}" />
	<link rel="icon" sizes="192x192" href="{!! Vite::asset('resources/img/event-logo-192.png') !!}" />

	@prepend('styles')
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	@endprepend
	@stack('styles')

	@prepend('modules')
		@vite('resources/js/legacy/app.js')
		@kiosk(true)
			@vite('resources/js/legacy/auto-logout.js')
		@endkiosk
	@endprepend
	@stack('modules')
</head>
<body>
	<div class="container my-4 my-md-5 {!! config('debugbar.enabled') ?? config('app.debug') ? 'mb-5 pb-5' : '' !!}">
		<header class="mb-3 mb-md-4 text-center">
			<a href="{!! route('tracker.index') !!}" title="{{ config('app.name') }}">
				<img src="{!! Vite::asset('resources/img/event-logo.png') !!}" width="128" height="146" alt="Event Logo" class="img-fluid mw-25" />
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
				const logoutTime =  @devMode 3600 @else @section('logoutTime') 60 @show @enddevMode;
			@endauth

			@if(Session::has('success'))
				const flashSuccess = {{ Js::from(Session::get('success')) }};
			@elseif(Session::has('error'))
				const flashError = {{ Js::from(Session::get('error')) }};
			@endif
		</script>
	@endprepend
	@stack('scripts')
</body>
</html>
