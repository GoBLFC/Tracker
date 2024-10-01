@extends('layouts.master')

@section('content')
	<div class="row justify-content-center mt-4 mt-md-5">
		<div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
			<div class="card mb-3">
				<div class="card-body">
					<h2 class="text-center mb-3">BLFC Volunteer Check-In</h2>
					<div class="alert alert-light text-center" role="alert">Welcome! Click below to sign in.</div>
					<a class="btn btn-success d-block mb-3" href="{!! route('auth.redirect') !!}" role="button">Sign In</a>

					<div class="card mt-5">
						<h3 class="h5 card-header text-center">
							Quick Sign-in Code <span data-bs-toggle="tooltip" data-bs-title="Link your Telegram account after you sign in above to get a quick code anytime!"><i class="fa-regular fa-circle-question"></i></span>
						</h3>
						<div class="card-body">
							<form id="form" autocomplete="off">
								<div class="input-group input-group-lg mb-3">
									<input type="text" maxlength="1" pattern="[a-zA-Z0-9]" class="form-control text-center font-monospace fs-2" autofocus="on" />
									<input type="text" maxlength="1" pattern="[a-zA-Z0-9]" class="form-control text-center font-monospace fs-2" />
									<input type="text" maxlength="1" pattern="[a-zA-Z0-9]" class="form-control text-center font-monospace fs-2" />
									<input type="text" maxlength="1" pattern="[a-zA-Z0-9]" class="form-control text-center font-monospace fs-2" />
								</div>
								<button type="button" class="btn btn-primary w-100" id="btnLogin">Sign In</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('footer-class', 'row justify-content-sm-center')
@section('footer-nav-class', 'col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4')

@push('modules')
	@vite('resources/js/legacy/login.js')
@endpush

@push('scripts')
	<script type="text/javascript">
		const quickcodePostUrl = '{!! route('auth.quickcode.post') !!}';
	</script>
@endpush
