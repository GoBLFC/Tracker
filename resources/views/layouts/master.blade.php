<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>{{ config('app.name') }}</title>

	@section('styles')
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/css/bootstrap.min.css" integrity="sha512-72OVeAaPeV8n3BdZj7hOkaPSEk/uwpDkaGyP4W2jSzAC8tfiO4LMEDWoL3uFp5mcZu+8Eehb4GhZWFwvrss69Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css" integrity="sha512-g2SduJKxa4Lbn3GW+Q7rNz+pKP9AWMR++Ta8fgwsZRCUsawjPvF/BxSMkGS61VsR9yinGoEgrHPGPn2mrj8+4w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempus-dominus/6.7.11/css/tempus-dominus.min.css" integrity="sha512-wO+rVZhTyJgwKxVY279cD/TZTlW2m0IJQXzoOHfj2w//md58T3jc8ZWHb+HEm8CspcCNnaJVFPyRAGd/Y4ScfA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5.0.15/dark.min.css" integrity="sha256-Dtn0fzAID6WRybYFj3UI5JDBy9kE2adX1xPUlW+B4XQ=" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" integrity="sha512-c42qTSw/wPZ3/5LBzD+Bw5f7bSF2oxou6wEb+I/lqeaKV5FDIfMvvRp772y4jcJLKuGUOpbJMdg/BTl50fJYAw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	@show
</head>
<body>
	<div class="container my-5">
		<img class="d-block mx-auto mb-4" src="{!! Vite::asset('resources/img/blfc-chip.png') !!}" width="128" height="146" />

		@devmode
			<div class="text-center mb-3">
				<span class="badge rounded-pill text-bg-warning">Dev Mode Enabled</span>

				@auth
					<span class="badge rounded-pill text-bg-primary">Your ID: {!! Auth::user()->id !!}</span>
					<span class="badge rounded-pill text-bg-secondary">Role: {!! Auth::user()->role->name !!}</span>
				@endauth

				@kiosk
					<span class="badge rounded-pill text-bg-success">Kiosk: Authorized</span>
				@else
					<span class="badge rounded-pill text-bg-danger">Kiosk: Unauthorized</span>
				@endkiosk

				@if($activeEvent !== null)
					<span class="badge rounded-pill text-bg-success">Event: {{ $activeEvent->name }}</span>
				@else
					<span class="badge rounded-pill text-bg-danger">Event: None active</span>
				@endif
			</div>
		@enddevmode

		@yield('content')

		@auth
			@include('partials.auto-logout')
		@endauth

		<button class="btn btn-sm btn-light float-end me-2" data-bs-toggle="modal" data-bs-target="#aboutModal">About</button>
	</div>

	@section('modals')
		@include('partials.about-modal')
	@show

	@section('scripts')
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js" integrity="sha512-TPh2Oxlg1zp+kz3nFA0C5vVC6leG/6mm1z9+mA81MI5eaUVqasPLO8Cuk4gMF4gUfP5etR73rgU/8PNMsSesoQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/js/bootstrap.min.js" integrity="sha512-eHx4nbBTkIr2i0m9SANm/cczPESd0DUEcfl84JpIuutE6oDxPhXvskMR08Wmvmfx5wUpVjlWdi82G5YLvqqJdA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/tempus-dominus/6.7.11/js/tempus-dominus.min.js" integrity="sha512-i94cZlKTXukGU4KHUYqR6C1vzcITMr6MycUwws27RdFj04GgJoX98/pfE93Pb5MuSyY5sFVX0pPTw8eGRFsp5g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.20/sweetalert2.min.js" integrity="sha512-2AOp8GEFv1X43dZpYqOp34WD+skmM18yOrCxS/S1Mh6VShz7uxlPhKmA98fsPrE7MMMtZgjshiMHKmzWtZR5uA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js" integrity="sha512-yrOmjPdp8qH8hgLfWpSFhC/+R9Cj9USL8uJxYIveJZGAiedxyIxwNw4RsLDlcjNlIRR4kkHaDHSmNHAkxFTmgg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/luxon/3.3.0/luxon.min.js" integrity="sha512-KKbQg5o92MwtJKR9sfm/HkREzfyzNMiKPIQ7i7SZOxwEdiNCm4Svayn2DBq7MKEdrqPJUOSIpy1v6PpFlCQ0YA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

		<script type="text/javascript">
			var _token = '{!! csrf_token() !!}';
		</script>
	@show
</body>
</html>
