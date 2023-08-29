@extends('layouts.master')

@section('content')
	<div class="card mb-3">
		<h5 class="card-header">Welcome, {{ Auth::user()->getDisplayName() }}!</h5>

		<div class="card-body">
			<div class="row gx-3 gy-2">
				@activeEvent
					@kiosk
						<div class="col-md">
							<div id="checkstatus" class="alert alert-{!! $ongoing ? 'success' : 'danger' !!} py-2 mb-0 h-100 d-flex align-items-center" role="alert">
								You are currently {!! !$ongoing ? 'not' : '' !!} checked in.
							</div>
						</div>

						<div class="col-md">
							<select class="form-select h-100" {!! $ongoing ? 'disabled ' : '' !!}id="dept">
								@if(!$ongoing)
									<option value="" disabled selected hidden>Select Department</option>
								@endif
								@foreach($departments->sortBy(['hidden', 'name']) as $dept)
									<option {!! $ongoing && $ongoing->department_id === $dept->id ? 'selected' : '' !!} value="{!! $dept->id !!}">
										{{ $dept->name . ($dept->hidden ? ' (hidden)' : '') }}
									</option>
								@endforeach
							</select>
						</div>

						<div class="col-md">
							<button id="checkinout" class="btn btn-primary w-100 h-100" data-value="{!! $ongoing ? 'out' : 'in' !!}" {!! !$ongoing ? 'disabled' : '' !!}>Check-{!! $ongoing ? 'Out' : 'In' !!}</button>
						</div>
					@else
						<div class="col-md">
							<div class="alert alert-warning mb-0" role="alert">
								<p class="mb-0">
									Checking in/out can only be done from authorized devices.
									Please visit the volunteer desk to do so.
								</p>
							</div>
						</div>
					@endkiosk
				@else
					<div class="col-md">
						<div class="alert alert-info mb-0" role="alert">
							<p class="mb-0">
								There isn't currently any event running to track time for.
							</p>
						</div>
					</div>
				@endactiveEvent
			</div>
		</div>
	</div>

	@lead
		@include('partials.management-nav')
	@endlead

	@activeEvent
		<div class="card mb-3">
			<h5 class="card-header">Your Stats</h5>

			<div class="card-body">
				<div class="row">

					<div @class(['col-md', 'text-center', 'py-4', 'd-none' => !$ongoing]) id="currdurr">
						<div class="display-2 fw-normal">
							<i class="fa-regular fa-clock"></i> <span id="durrval">{!! $ongoing?->getClockDuration() ?? '' !!}</span>
						</div>
						<div class="text-uppercase">
							Shift Duration
							<span data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="The amount of time that has elapsed since your last check-in."><i class="fa-regular fa-circle-question"></i></span>
						</div>
					</div>

					<div class="col-md text-center py-4">
						<div class="display-2 fw-normal">
							<i class="fa-regular fa-clock"></i> <span id="timetoday">{!! \App\Models\TimeEntry::humanDuration($stats['day']) !!}</span>
						</div>
						<div class="text-uppercase">
							Time Today
							<span data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="The amount of time that you have volunteered today. This does not include bonus time."><i class="fa-regular fa-circle-question"></i></span>
						</div>
					</div>

					<div class="col-md text-center py-4">
						<div class="display-2 fw-normal">
							<i class="fa-regular fa-clock"></i> <span id="earnedtime">{!! \App\Models\TimeEntry::humanDuration($stats['total']) !!}</span>
						</div>
						<div class="text-uppercase">
							Time Earned
							<span data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="The total amount of time you've volunteered for the convention so far. This includes time bonuses that may apply to you, if any."><i class="fa-regular fa-circle-question"></i></span>
						</div>
					</div>

				</div>
			</div>
		</div>
	@endactiveEvent
@endsection

@section('footer-nav')
	@parent
	<button class="btn btn-lg btn-info" data-bs-toggle="modal" data-bs-target="#telegramModal"><i class="fa-brands fa-telegram"></i> Telegram Bot</button>
@endsection

@push('modals')
	@include('partials.telegram-modal')
@endpush

@push('modules')
	@vite('resources/js/time.js')
@endpush

@push('scripts')
	<script type="text/javascript">
		const checkinPostUrl = '{!! route('tracker.checkin.post') !!}';
		const checkoutPostUrl = '{!! route('tracker.checkout.post') !!}';
		const time = {
			total: {!! $stats['total'] !!} * 1000,
			day: {!! $stats['day'] !!} * 1000,
			ongoingStart: {!! $ongoing?->start?->timestamp * 1000 ?? 'null' !!},
		};
	</script>
@endpush
