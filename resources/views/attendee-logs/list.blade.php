@extends('layouts.master')

@section('content')
	<div class="card mb-3">
		<h4 class="card-header text-bg-info">Attendee Logs</h4>

		<div class="card-body">
			@include('partials.event-selector', [
				'actionWord' => 'Manage',
				'cardClass' => 'mb-3',
				'route' => route('events.attendee-logs.index', 'event-id'),
			])

			@if($event)
				@unless($event->isActive())
					<div class="alert alert-info" role="alert">
						<p class="mb-0">
							You are managing data for an inactive event.
							@unlessadmin
								Everything will be read-only.
							@endunless
						</p>
					</div>
				@endunless
			@else
				<div class="alert alert-info" role="alert">
					<p class="mb-0">
						There isn't currently any event running, and you haven't selected one above.
					</p>
				</div>
			@endif

			<div class="card">
				<div class="card-header">Attendee Logs</div>

				@if($attendeeLogs->isNotEmpty())
					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table table-dark table-striped w-100 mb-0">
								<thead>
									<tr>
										<th scope="col">Name</th>
										<th scope="col">Attendees</th>
										<th scope="col">Gatekeepers</th>
										<th scope="col">Total</th>
									</tr>
								</thead>
								<tbody>
									@foreach($attendeeLogs as $log)
										<tr>
											<td>
												<a href="{!! route('attendee-logs.show', $log->id) !!}">
													{{ $log->display_name }}
												</a>
											</td>
											<td>{!! $log->attendees_count !!}</td>
											<td>{!! $log->gatekeepers_count !!}</td>
											<td>{!! $log->users_count !!}</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				@else
					<p class="card-body mb-0">There are no attendee logs.</p>
				@endif
			</div>
		</div>
	</div>

	@include('partials.management-nav', ['cardClass' => 'mb-3 mt-5'])
@endsection
