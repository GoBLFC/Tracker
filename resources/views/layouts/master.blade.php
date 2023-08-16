<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>{{ config('app.name') }}</title>

	@prepend('styles')
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		@vite('resources/sass/app.scss')
	@endprepend
	@stack('styles')

	@prepend('modules')
		@vite('resources/js/app.js')
	@endprepend
	@stack('modules')
</head>
<body>
	<div class="container my-5">
		<img class="d-block mx-auto mb-4" src="{!! Vite::asset('resources/img/blfc-chip.png') !!}" width="128" height="146" />

		@devMode
			<div class="text-center mb-3">
				<span class="badge rounded-pill text-bg-warning">Dev Mode Enabled</span>

				@auth
					<span class="badge rounded-pill text-bg-primary">Your Badge ID: {!! Auth::user()->badge_id !!}</span>
					<span class="badge rounded-pill text-bg-primary">Your UUID: {!! Auth::user()->id !!}</span>
					<span class="badge rounded-pill text-bg-secondary">Role: {!! Auth::user()->role->name !!}</span>
				@endauth

				@kiosk(true)
					<span id="devKioskStatus" class="badge rounded-pill text-bg-success">Kiosk: Authorized</span>
				@else
					<span id="devKioskStatus" class="badge rounded-pill text-bg-danger">Kiosk: Unauthorized</span>
				@endkiosk

				@php $activeEvent = \App\Models\Setting::activeEvent(); @endphp
				@if($activeEvent)
					<span class="badge rounded-pill text-bg-success">Event: {{ $activeEvent->name }}</span>
				@else
					<span class="badge rounded-pill text-bg-danger">Event: None active</span>
				@endif
			</div>
		@enddevMode

		@yield('content')

		@auth
			@include('partials.auto-logout')
		@endauth

		<button class="btn btn-sm btn-light float-end me-2" data-bs-toggle="modal" data-bs-target="#aboutModal">About</button>
	</div>

	@prepend('modals')
		@include('partials.about-modal')
	@endprepend
	@stack('modals')

	@prepend('scripts')
		<script type="text/javascript">
			const _token = '{!! csrf_token() !!}';
		</script>
	@endprepend
	@stack('scripts')
</body>
</html>
