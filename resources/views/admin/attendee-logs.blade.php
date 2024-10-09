@extends('layouts.admin')

@section('admin-content')
	<div class="card mb-3">
		<h4 class="card-header">Attendee Log Management</h4>
		<div class="card-body">
			<p>
				Attendee Logs are for tracking everybody who attends a scheduled event.
				Managers and Admins can add "Gatekeepers" to an Attendee log to give them the ability to add users to the log.
			</p>

			@include('partials.event-selector', ['route' => route('admin.event.attendee-logs', 'event-id')])

			@if($event)
				<div class="card mb-4">
					<h5 class="card-header">Attendee Logs</h5>

					@if(!$attendeeLogs->isEmpty())
						<div class="card-body p-0">
							<div class="table-responsive">
								<table class="table table-dark table-striped mb-0">
									<thead>
										<tr>
											<th scope="col">Name</th>
											<th scope="col">Gatekeepers</th>
											<th scope="col"></th>
											<th scope="col"></th>
										</tr>
									</thead>
									<tbody class="align-middle">
										@foreach($attendeeLogs->sortBy('name') as $log)
											<tr>
												<td class="w-50">
													<input form="update-{!! $log->id !!}"
														type="text"
														class="form-control attendeeLogName"
														name="name"
														value="{{ $log->display_name }}"
														required />
												</td>
												<td class="w-50">
													<ul>
														@foreach($log->gatekeepers as $gatekeeper)
															<li>{{ $gatekeeper->audit_name }}</li>
														@endforeach
													</ul>
												</td>
												<td>
													<form action="{!! route('attendee-logs.update', $log->id) !!}" method="POST" id="update-{!! $log->id !!}" class="seamless update">
														@method('PUT')
														@csrf
														<button type="submit" class="btn btn-success float-end" data-success="Updated attendee log.">Save</button>
													</form>
												</td>
												<td>
													<form action="{!! route('attendee-logs.destroy', $log->id) !!}" method="POST" id="delete-{!! $log->id !!}" class="seamless delete">
														@method('DELETE')
														@csrf
														<button type="submit"
															class="btn btn-danger float-end"
															data-success="Deleted attendee log."
															data-confirm-title="Delete attendee log?"
															data-confirm-text="{{ $log->display_name }}">

															Delete
														</button>
													</form>
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					@else
						<div class="card-body">
							<p class="mb-0">There are no attendee logs.</p>
						</div>
					@endif
				</div>

				<div class="card">
					<h5 class="card-header">Create Attendee Log</h5>
					<div class="card-body">
						<form action="{!! route('events.attendee-logs.store', $event->id) !!}" method="POST" id="attendeeLogCreate" class="seamless">
							@csrf
							<div class="input-group">
								<label for="attendeeLogName" class="input-group-text">Name</label>
								<input type="text" name="name" id="attendeeLogName" class="form-control" required />
								<button type="submit" class="btn btn-success">Create</button>
							</div>
						</form>
					</div>
				</div>
			@else
				<div class="alert alert-info mb-0" role="alert">Please select an event to manage the attendee logs for.</div>
			@endif
		</div>
	</div>
@endsection

@push('modules')
	@vite('resources/js/legacy/seamless-forms.js')
	@vite('resources/js/legacy/admin/attendee-logs.js')
@endpush
