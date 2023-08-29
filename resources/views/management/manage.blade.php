@extends('layouts.master')

@section('content')
	<div class="card mb-3">
		<h4 class="card-header text-bg-warning">Management Controls</h4>

		<div class="card-body">

			@unlessactiveEvent
				<div class="alert alert-info" role="alert">
					<p class="mb-0">
						There isn't currently any event running.
						Most time-related information/functionality will be unavailable.
					</p>
				</div>
			@endactiveEvent

			<div class="card mb-4">
				<div class="card-header">Search Volunteers</div>

				<div class="card-body">
					<input id="searchinput" type="text" class="form-control" placeholder="Badge Number, Name, Username..." aria-label="Search" />

					<div id="usearchCard" class="card mt-3 d-none">
						<p id="uempty" class="card-body mb-0">There are no users that match your search.</p>

						<div class="card-body p-0">
							<div class="table-responsive">
								<table id="utable" class="table table-striped w-100 mb-0">
									<thead>
										<tr>
											<th scope="col">ID</th>
											<th scope="col">Username</th>
											<th scope="col">Badge Name</th>
											<th scope="col">Real Name</th>
											<th scope="col">Status</th>
											<th scope="col"></th>
										</tr>
									</thead>
									<tbody id="uRow"></tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="userCard" class="card border-info-subtle mb-5 d-none">
				<h5 id="userCardTitle" class="card-header text-center">User</h5>

				<div class="card-body">

					<div class="row">
						<div class="col-md text-center py-4 d-none" id="currdurr">
							<div class="display-2 fw-normal">
								<i class="fa-regular fa-clock"></i> <span id="durrval">Loading</span>
							</div>
							<div class="text-uppercase">Shift Duration</div>
						</div>

						<div class="col-md text-center py-4">
							<div class="display-2 fw-normal">
								<i class="fa-regular fa-clock"></i> <span id="timetoday">Loading</span>
							</div>
							<div class="text-uppercase">Hours Today</div>
						</div>

						<div class="col-md text-center py-4">
							<div class="display-2 fw-normal">
								<i class="fa-regular fa-clock"></i> <span id="earnedtime">Loading</span>
							</div>
							<div class="text-uppercase">Hours Earned</div>
						</div>
					</div>

					<div class="card mb-3">
						<div class="card-header">Reward Claims</div>
						@if($rewards->count() > 0)
							<div class="card-body p-0">
								<div class="table-responsive">
									<table class="table table-borderless text-center mb-0">
										<thead>
											<tr>
												@foreach($rewards as $reward)
													<th scope="col" class="pt-3">{!! $reward->hours !!}hr: {{ $reward->name }}</th>
												@endforeach
											</tr>
										</thead>
										<tbody>
											<tr id="rewards">
												@foreach($rewards as $reward)
													<td class="pb-3">
														<button type="button" class="btn btn-sm btn-danger claim" data-type="reward" data-reward-id="{!! $reward->id !!}">Claim</button>
													</td>
												@endforeach
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						@else
							<p class="card-body mb-0">There are no rewards available.</p>
						@endif
					</div>

					<div class="card mb-3">
						<div class="card-header">Time Log</div>
						<p class="card-body mb-0" id="eNone">This user doesn't have any time entries.</p>
						<div class="card-body p-0 d-none" id="eSome">
							<div class="table-responsive">
								<table id="table" class="table table-striped mb-0">
									<thead>
										<tr>
											<th scope="col">In</th>
											<th scope="col">Out</th>
											<th scope="col">Department</th>
											<th scope="col">Worked</th>
											<th scope="col">Earned</th>
											<th scope="col">Notes</th>
											<th scope="col" class="text-end">Actions</th>
										</tr>
									</thead>
									<tbody id="eRow"></tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="card">
						<div class="card-header">Add Time</div>
						<div class="card-body">

							<div class="row gx-3">

								<div class="col-md-4 col-lg-3 mb-2 mb-md-0">
									<div class="input-group" id="timeStart" data-td-target-input="nearest" data-td-target-toggle="nearest">
										<input id="timeStartInput" type="text" class="form-control" data-td-target="#timeStart" placeholder="Start" />
										<span class="input-group-text" data-td-target="#timeStart" data-td-toggle="datetimepicker">
											<i class="fa-solid fa-calendar"></i>
										</span>
									</div>
								</div>

								<div class="col-md-4 col-lg-3 mb-2 mb-md-0">
									<div class="input-group" id="timeStop" data-td-target-input="nearest" data-td-target-toggle="nearest">
										<input id="timeStopInput" type="text" class="form-control" data-td-target="#timeStop" placeholder="Stop" />
										<span class="input-group-text" data-td-target="#timeStop" data-td-toggle="datetimepicker">
											<i class="fa-solid fa-calendar"></i>
										</span>
									</div>
								</div>

								<div class="col-md-4 col-lg-2 mb-2 mb-md-0">
									<select class="form-select w-100" title="Department" id="dept">
										<option value="" disabled selected hidden>Select Department</option>
										@foreach($departments as $dept)
											<option value="{!! $dept->id !!}">{{ $dept->name . ($dept->hidden ? ' (hidden)' : '') }}</option>
										@endforeach
									</select>
								</div>

								<div class="col-md-12 col-lg-4 mt-md-3 mt-lg-0">
									<div class="input-group">
										<input type="text" class="form-control" placeholder="Notes" aria-label="Notes" id="notes" />
										<button id="checkin" class="btn btn-success" type="button" disabled>Check In</button>
										<button id="addtime" class="btn btn-success" type="button" disabled>Add Time</button>
									</div>
								</div>

							</div>

						</div>
					</div>

				</div>
			</div>

			<div class="card mb-4">
				<div class="card-header">Recent Check-in / Check-out</div>

				@if($recentTimeActivities->isNotEmpty())
					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table table-striped w-100 mb-0">
								<thead>
									<tr>
										<th scope="col">ID</th>
										<th scope="col">Username</th>
										<th scope="col">Badge Name</th>
										<th scope="col">Real Name</th>
										<th scope="col">Action</th>
										<th scope="col">Time</th>
										<th scope="col">Duration</th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody id="uRow">
									@foreach($recentTimeActivities as $activity)
										<tr>
											<th scope="row">{!! $activity->subject->user->badge_id !!}</th>
											<td>{{ $activity->subject->user->username }}</td>
											<td>{{ $activity->subject->user->badge_name }}</td>
											<td>{{ $activity->subject->user->getRealName() }}</td>
											<td>
												@if(isset($activity->properties['attributes']['stop']))
													<span class="badge text-bg-warning rounded-pill">Checked Out</span>
												@else
													<span class="badge text-bg-success rounded-pill">Checked In</span>
												@endif
											</td>
											<td>{!! $activity->created_at->timezone(config('tracker.timezone'))->toDayDateTimeString() !!}</td>
											<td>{!! $activity->subject->getHumanDuration() !!}</td>
											<td>
												<button class="btn btn-link btn-sm link-info float-end mx-1 p-0" data-user-id="{!! $activity->subject->user->id !!}" title="Lookup user">
													<i class="fa fa-magnifying-glass"></i>
												</button>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				@else
					<p class="card-body mb-0">There are no recent check-ins/check-outs.</p>
				@endif
			</div>

			<div class="card mb-4">
				<div class="card-header">Longest Ongoing Shifts</div>

				@if($longestOngoingEntries->isNotEmpty())
					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table table-striped w-100 mb-0">
								<thead>
									<tr>
										<th scope="col">ID</th>
										<th scope="col">Username</th>
										<th scope="col">Badge Name</th>
										<th scope="col">Real Name</th>
										<th scope="col">Department</th>
										<th scope="col">Start Time</th>
										<th scope="col">Duration</th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody id="uRow">
									@foreach($longestOngoingEntries as $entry)
										<tr>
											<th scope="row">{!! $entry->user->badge_id !!}</th>
											<td>{{ $entry->user->username }}</td>
											<td>{{ $entry->user->badge_name }}</td>
											<td>{{ $entry->user->getRealName() }}</td>
											<td>{{ $entry->department->name }}</td>
											<td>{!! $entry->start->timezone(config('tracker.timezone'))->toDayDateTimeString() !!}</td>
											<td>{!! $entry->getHumanDuration() !!}</td>
											<td>
												<button class="btn btn-link btn-sm link-info float-end mx-1 p-0" data-user-id="{!! $entry->user->id !!}" title="Lookup user">
													<i class="fa fa-magnifying-glass"></i>
												</button>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				@else
					<p class="card-body mb-0">There are no ongoing shifts.</p>
				@endif
			</div>

			<div class="card mb-4">
				<div class="card-header">Create User</div>
				<div class="card-body">
					<div class="input-group">
						<input type="text" inputmode="numeric" pattern="[0-9]+" class="form-control" placeholder="Badge Number" aria-label="Badge Number" />
						<button id="createUser" class="btn btn-success" type="button">
							Create User
						</button>
					</div>
				</div>
			</div>

			<div class="card mb-4">
				<div class="card-header">Kiosk Settings</div>
				<div class="card-body">
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
@endsection

@push('modules')
	@vite('resources/js/manage.js')
@endpush

@push('scripts')
	<script type="text/javascript">
		const timezone = '{!! config('tracker.timezone') !!}';
		const trackerStatsUrl = '{!! route('tracker.user.stats', 'id') !!}';
		const userClaimsUrl = '{!! route('user.claims', 'id') !!}';
		const userClaimsPutUrl = '{!! route('user.claims.put', 'id') !!}';
		const userClaimsDeleteUrl = '{!! route('user.claims.delete', 'id') !!}';
		const userSearchUrl = '{!! route('user.search') !!}';
		const userPutUrl = '{!! route('user.put') !!}';
		const timeCheckoutPostUrl = '{!! route('tracker.time.checkout.post', 'id') !!}';
		const timePutUrl = '{!! route('tracker.time.put', 'id') !!}';
		const timeDeleteUrl = '{!! route('tracker.time.delete', 'id') !!}';
		const departments = {{ Js::from($departments) }};
		const rewards = {{ JS::from($rewards) }};
	</script>
@endpush
