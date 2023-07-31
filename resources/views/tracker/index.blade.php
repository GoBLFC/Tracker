@extends('layouts.master')

@section('content')
	<div class="card mb-3">
		<h5 class="card-header">Welcome, {{ $user->badge_name ?? $user->username }}!</h5>

		<div class="card-body">
			<div class="row">

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
						@foreach($departments as $dept)
							<option {!! $ongoing && $ongoing->department_id === $dept->id ? 'selected' : '' !!} value="{!! $dept->id !!}">
								{{ $dept->name . ($dept->hidden ? ' (hidden)' : '') }}
							</option>
						@endforeach
					</select>
				</div>

				<div class="col-md">
					<button id="checkinout" class="btn btn-primary w-100 h-100" data-value="{!! $ongoing ? 'out' : 'in' !!}" {!! !$ongoing ? 'disabled' : '' !!}>Check-{!! $ongoing ? 'Out' : 'In' !!}</button>
				</div>

			</div>
		</div>
	</div>

	@if($user->isLead())
		@include('partials.management-nav')
	@endif

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

	<button class="btn btn-lg btn-info" data-bs-toggle="modal" data-bs-target="#telegramModal"><i class="fa-brands fa-telegram"></i> Telegram Bot</button>
@endsection

@section('scripts')
	@parent

	<script type="text/javascript">
		var checkinPostUrl = '{!! route('tracker.checkin.post') !!}';
		var checkoutPostUrl = '{!! route('tracker.checkout.post') !!}';
		var time = {
			total: {!! $stats['total'] !!} * 1000,
			day: {!! $stats['day'] !!} * 1000,
			ongoingStart: {!! $ongoing?->start?->timestamp * 1000 ?? 'null' !!},
		};
	</script>

	@vite('resources/js/tracker.js')
@endsection
