@extends('layouts.master')

@section('content')
	<div class="row justify-content-md-center">
		<div class="col-md-10 col-lg-8">
			<div class="card mb-3">
				<h4 class="card-header text-bg-warning">Lead Controls</h4>

				<div class="card-body">
					<div class="card mb-3">
						<div class="card-header">Kiosk Settings</div>
						<div class="card-body row">
							<dl class="mb-0">
								{{-- Kiosk toggle --}}
								<div class="row">
									<dt class="col-xl-4 col-md-4 col-sm-12 mb-2 mb-md-0 align-self-center text-center">
										@include('partials.toggle-kiosk-button', ['kioskToggleClasses' => 'btn float-md-end'])
									</dt>
									<dd class="col-xl-6 col-md-8 col-sm-12 mb-0">
										<p class="mb-0">
											Authorizing this device as a kiosk will allow volunteers to check in or out on this device.
											This is required when setting up dedicated devices pre-con for checking in or out.
											Kiosks remain authorized for {!! Carbon\CarbonInterval::minutes(config('tracker.kiosk_lifetime'))->cascade()->forHumans() !!}.
										</p>
									</dd>
								</div>
							</dl>
						</div>
					</div>

					<a class="btn btn-primary float-end" href="{!! route('tracker.index') !!}" role="button">Back</a>
				</div>
			</div>

			@manager
				@include('partials.management-nav', ['cardClass' => 'mb-3 mt-4'])
			@endmanager
		</div>
	</div>
@endsection

@section('footer-class', 'row justify-content-md-center')
@section('footer-nav-class', 'col-md-10 col-lg-8')
