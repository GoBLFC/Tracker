@extends('layouts.master')

@section('content')
	<div class="row justify-content-md-center mt-5">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h2 class="text-center">BLFC Volunteer Check-In</h2>
					<div class="alert alert-light text-center" role="alert">Welcome! Click below to sign in.</div>
					<a class="btn btn-success d-block mb-3" href="{!! route('auth.redirect') !!}" role="button">Sign In</a>

					<div class="card mt-5">
						<h5 class="card-header text-center">
							Quick Sign-in Code <span data-bs-toggle="tooltip" data-bs-title="Link your Telegram account after you sign in above to get a quick code anytime!"><i class="fa-regular fa-circle-question"></i></span>
						</h5>
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

@section('scripts')
	@parent

	<script type="text/javascript">
		var quickcodePostUrl = '{!! route('auth.quickcode.post') !!}';
	</script>

	@vite('resources/js/login.js')
@endsection
