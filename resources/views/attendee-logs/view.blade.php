@extends('layouts.master')

@section('content')
	<div class="card mb-3">
		<h4 class="card-header text-bg-info">Attendee Logs</h4>

		<div class="card-body">
			@unless($attendeeLog->event->isActive())
				<div class="alert alert-info" role="alert">
					<p class="mb-0">
						You are managing data for an inactive event.
						@unlessadmin
							Everything will be read-only.
						@endunless
					</p>
				</div>
			@endunless

			@php($readOnly = !$attendeeLog->isForActiveEvent() && !Auth::user()->isAdmin())

			@if(!$readOnly)
				<div class="card mb-3">
					<h5 class="card-header">Log Attendee</h5>
					<div class="card-body">
						<form action="{!! route('attendee-logs.users.store', $attendeeLog->id) !!}" method="POST" id="userLog" class="seamless">
							@method('PUT')
							@csrf
							<div class="input-group">
								<label for="badgeId" class="input-group-text">Badge ID</label>
								<input type="text" inputmode="numeric" pattern="[0-9]+" name="badge_id" id="logBadgeId" class="form-control" required autofocus />
								<button type="submit" class="btn btn-success" data-success="Logged attendee.">Log</button>
							</div>
						</form>
					</div>
				</div>

				@manager
					<div class="card mb-3">
						<h5 class="card-header">Add Gatekeeper</h5>
						<div class="card-body">
							<p>
								Gatekeepers can view, add, and delete attendees in the log, but cannot manage gatekeepers themselves.
								Any volunteer, not just staff, can be added as a gatekeeper.
							</p>

							<form action="{!! route('attendee-logs.users.store', $attendeeLog->id) !!}" method="POST" id="gatekeeperAdd" class="seamless">
								@method('PUT')
								@csrf
								<input type="hidden" name="type" value="gatekeeper" />
								<div class="input-group">
									<label for="badgeId" class="input-group-text">Badge ID</label>
									<input type="text" inputmode="numeric" pattern="[0-9]+" name="badge_id" id="gatekeeperBadgeId" class="form-control" required autofocus />
									<button type="submit" class="btn btn-warning" data-success="Empowered gatekeeper.">Empower Gatekeeper</button>
								</div>
							</form>
						</div>
					</div>
				@endmanager
			@endif

			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="mb-0">{{ $attendeeLog->display_name }}</h5>

					<div class="btn-group" role="group">
						<button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
							Export
						</button>
						<ul class="dropdown-menu">
							@foreach($exportTypes as $extension => $label)
								@php($route = route('admin.event.reports.export', [$attendeeLog->event_id, 'attendee-log', $extension]))
								<li><a class="dropdown-item" href="{!!
									route('admin.event.reports.export', [
										$attendeeLog->event_id,
										'attendee-log',
										$extension,
										'id' => $attendeeLog->id,
									])
								!!}">{{ $label }}</a></li>
							@endforeach
						</ul>
					</div>
				</div>

				<div id="users-body" class="card-body p-0 {!! $attendeeLog->users->isEmpty() ? 'd-none' : '' !!}">
					<div class="table-responsive">
						<table class="table table-dark table-striped w-100 mb-0">
							<thead>
								<tr>
									<th scope="col">ID</th>
									<th scope="col">Badge Name</th>
									<th scope="col">Type</th>
									<th scope="col">Logged</th>
									@if(!$readOnly)
										<th scope="col"></th>
									@endif
								</tr>
							</thead>
							<tbody>
								@foreach($attendeeLog->users as $user)
									<tr>
										<th scope="row">{!! $user->badge_id !!}</th>
										<td>{{ $user->badge_name }}</td>
										<td>
											@php($isGatekeeper = $user->pivot->type === 'gatekeeper')
											<span @class(['badge', 'rounded-pill', 'text-bg-warning' => $isGatekeeper, 'text-bg-secondary' => !$isGatekeeper])>
												{!! Str::title($user->pivot->type) !!}
											</span>
										</td>
										<td>{!! $user->pivot->created_at->timezone(config('tracker.timezone'))->toDayDateTimeString() !!}</td>
										@if(!$readOnly)
											<td>
												<form action="{!! route('attendee-logs.users.destroy', [$attendeeLog->id, $user->id]) !!}" method="POST" id="delete-{!! $user->id !!}" class="seamless delete">
													@method('DELETE')
													@csrf
													<button type="submit"
														class="btn btn-sm btn-danger float-end"
														data-success="Deleted {!! $user->pivot->type !!}."
														data-confirm-title="Delete {!! $user->pivot->type !!}?"
														data-confirm-text="{{ $user->audit_name }}">

														Delete
													</button>
												</form>
											</td>
										@endif
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
				<p id="users-body-empty" class="card-body mb-0 {!! $attendeeLog->users->isNotEmpty() ? 'd-none' : '' !!}">There are no attendees logged.</p>
			</div>

			<a class="btn btn-primary float-end mt-3" href="{!! route('events.attendee-logs.index', $attendeeLog->event->id) !!}" role="button">Back</a>
		</div>
	</div>

	@include('partials.management-nav', ['cardClass' => 'mb-3 mt-5'])
@endsection

@section('logoutTime', 1800)

@push('modules')
	@vite('resources/js/attendee-log.js')
@endpush

@push('scripts')
	<script type="text/javascript">
		const attendeeLogsUsersDestroyUrl = '{!! route('attendee-logs.users.destroy', ['attendee-log-id', 'user-id']) !!}';
		const attendeeLogId = '{!! $attendeeLog->id !!}';
	</script>
@endpush
