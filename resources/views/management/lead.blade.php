@extends('layouts.master')

@section('content')
	<div class="row justify-content-md-center">
		<div class="col-md-10 col-lg-8">
			<div class="card mb-3">
				<h4 class="card-header text-bg-warning">Lead</h4>

				<div class="card-body">
					<div class="card mb-3">
						<h5 class="card-header">Kiosk Settings</h5>
						<div class="card-body text-center">
							@include('partials.toggle-kiosk-button')
						</div>
					</div>

					<a class="btn btn-primary btn-sm float-end" href="{!! route('tracker.index') !!}" role="button">Back</a>
				</div>
			</div>
		</div>
	</div>
@endsection
