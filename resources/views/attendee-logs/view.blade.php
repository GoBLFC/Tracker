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

			<div class="card mb-3">
				<h5 class="card-header">{{ $attendeeLog->display_name }}</h5>
				<div id="users-body" class="card-body p-0 {!! $attendeeLog->users->isEmpty() ? 'd-none' : '' !!}">
					<div class="table-responsive">
						<table class="table table-dark table-striped w-100 mb-0">
							<thead>
								<tr>
									<th scope="col">ID</th>
									<th scope="col">Badge Name</th>
									<th scope="col">Logged</th>
									<th scope="col"></th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								@foreach($attendeeLog->users as $user)
									<tr>
										<th scope="row">{!! $user->badge_id !!}</th>
										<td>{{ $user->badge_name }}</td>
										<td>{!! $user->pivot->created_at->timezone(config('tracker.timezone'))->toDayDateTimeString() !!}</td>
										<td>
											@php($isGatekeeper = $user->pivot->type === 'gatekeeper')
											<span @class(['badge', 'rounded-pill', 'text-bg-warning' => $isGatekeeper, 'text-bg-secondary' => !$isGatekeeper])>
												{!! Str::title($user->pivot->type) !!}
											</span>
										</td>
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
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
				<p id="users-body-empty" class="card-body mb-0 {!! $attendeeLog->users->isNotEmpty() ? 'd-none' : '' !!}">There are no attendees logged.</p>
			</div>

			<div class="card">
				<h5 class="card-header">Log Attendee</h5>
				<div class="card-body">
					<form action="{!! route('attendee-logs.users.store', $attendeeLog->id) !!}" method="POST" id="userLog" class="seamless">
						@method('PUT')
						@csrf
						<div class="input-group">
							<label for="badgeId" class="input-group-text">Badge ID</label>
							<input type="text" inputmode="numeric" pattern="[0-9]+" name="badge_id" id="logBadgeId" class="form-control" required autofocus />
							<button type="submit" class="btn btn-success">Log</button>
						</div>
					</form>
				</div>
			</div>

			@manager
				<div class="card mt-3">
					<h5 class="card-header">Add Gatekeeper</h5>
					<div class="card-body">
						<form action="{!! route('attendee-logs.users.store', $attendeeLog->id) !!}" method="POST" id="gatekeeperAdd" class="seamless">
							@method('PUT')
							@csrf
							<input type="hidden" name="type" value="gatekeeper" />
							<div class="input-group">
								<label for="badgeId" class="input-group-text">Badge ID</label>
								<input type="text" inputmode="numeric" pattern="[0-9]+" name="badge_id" id="gatekeeperBadgeId" class="form-control" required autofocus />
								<button type="submit" class="btn btn-warning">Empower Gatekeeper</button>
							</div>
						</form>
					</div>
				</div>
			@endmanager
		</div>
	</div>

	@include('partials.management-nav', ['cardClass' => 'mb-3 mt-5'])
@endsection

@push('modules')
	@vite('resources/js/attendee-log.js')
@endpush

@push('scripts')
	<script type="text/javascript">
		const attendeeLogsUsersDestroyUrl = '{!! route('attendee-logs.users.destroy', ['attendee-log-id', 'user-id']) !!}';
		const attendeeLogId = '{!! $attendeeLog->id !!}';
	</script>
@endpush
