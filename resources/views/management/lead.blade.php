@extends('layouts.master')

@section('content')
	<div class="row justify-content-md-center">
		<div class="col-md-10 col-lg-8">
			<div class="card mb-3">
				<h4 class="card-header text-bg-warning">Lead Controls</h4>

				<div class="card-body">
					<div class="card mb-3">
						<div class="card-header">Kiosk Settings</div>
						<div class="card-body text-center">
							@include('partials.toggle-kiosk-button')
						</div>
					</div>

					<a class="btn btn-primary float-end" href="{!! route('tracker.index') !!}" role="button">Back</a>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('footer-class', 'row justify-content-md-center')
@section('footer-nav-class', 'col-md-10 col-lg-8')
