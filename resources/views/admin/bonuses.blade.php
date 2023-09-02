@extends('layouts.admin')

@section('admin-content')
	<div class="card mb-3">
		<h4 class="card-header">Time Bonus Management</h4>
		<div class="card-body">
			<p>
				Time Bonuses are periods of time during an event that volunteers working for a specific department get a multiplier for their time worked.
			</p>

			@include('partials.event-selector', ['route' => route('admin.event.bonuses', 'event-id')])

			@if($event)
				<div class="card mb-4">
					<h5 class="card-header">Time Bonuses</h5>

					@if($bonuses && !$bonuses->isEmpty())
						<div class="card-body p-0">
							<div class="table-responsive">
								<table class="table table-striped mb-0">
									<thead>
										<tr>
											<th scope="col">Start</th>
											<th scope="col">Stop</th>
											<th scope="col">Modifier</th>
											<th scope="col">Departments</th>
											<th scope="col"></th>
											<th scope="col"></th>
										</tr>
									</thead>
									<tbody class="align-middle">
										@foreach($bonuses->sortBy('start') as $bonus)
											<tr>
												<td class="w-33">
													<div id="start-{!! $bonus->id !!}" class="input-group bonusStart" data-td-target-input="nearest" data-td-target-toggle="nearest">
														<input form="update-{!! $bonus->id !!}"
															type="text"
															name="start"
															value="{!! $bonus->start->format('Y-m-d h:i:s A') !!}"
															required
															class="form-control"
															data-td-target="#start-{!! $bonus->id !!}" />
														<span class="input-group-text" data-td-target="#start-{!! $bonus->id !!}" data-td-toggle="datetimepicker">
															<i class="fa-solid fa-calendar"></i>
														</span>
													</div>
												</td>
												<td class="w-33">
													<div id="stop-{!! $bonus->id !!}" class="input-group bonusStop" data-td-target-input="nearest" data-td-target-toggle="nearest">
														<input form="update-{!! $bonus->id !!}"
															type="text"
															name="stop"
															value="{!! $bonus->stop->format('Y-m-d h:i:s A') !!}"
															required
															class="form-control"
															data-td-target="#stop-{!! $bonus->id !!}" />
														<span class="input-group-text" data-td-target="#stop-{!! $bonus->id !!}" data-td-toggle="datetimepicker">
															<i class="fa-solid fa-calendar"></i>
														</span>
													</div>
												</td>
												<td>
													<input form="update-{!! $bonus->id !!}"
														type="number"
														min="1"
														max="10"
														step="0.25"
														required
														name="modifier"
														value="{!! $bonus->modifier !!}"
														class="form-control me-0"
														style="width: 5em;" />
												</td>
												<td class="w-33">
													<select form="update-{!! $bonus->id !!}" name="departments[]" class="form-select" multiple required>
														@foreach($departments as $department)
															<option value="{!! $department->id !!}" {!! $bonus->departments->contains($department) ? 'selected' : '' !!}>
																{{ $department->name }} {!! $department->hidden ? '(hidden)' : '' !!}
															</option>
														@endforeach
													</select>
												</td>
												<td>
													<form action="{!! route('bonuses.update', $bonus->id) !!}" method="POST" id="update-{!! $bonus->id !!}" class="seamless update">
														@method('PUT')
														@csrf
														<button type="submit" class="btn btn-success float-end" data-success="Updated time bonus.">Save</button>
													</form>
												</td>
												<td>
													<form action="{!! route('bonuses.destroy', $bonus->id) !!}" method="POST" id="delete-{!! $bonus->id !!}" class="seamless delete">
														@method('DELETE')
														@csrf
														<button type="submit"
															class="btn btn-danger float-end"
															data-success="Deleted time bonus."
															data-confirm-title="Delete time bonus?"
															data-confirm-text="{{ $bonus->start->format('Y-m-d h:i:s A') }} &ndash; {{ $bonus->stop->format('Y-m-d h:i:s A') }}">

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
							<p class="mb-0">There are no time bonuses for the event.</p>
						</div>
					@endif
				</div>

				<div class="card">
					<h5 class="card-header">Create Time Bonus</h5>
					<div class="card-body">
						<form action="{!! route('events.bonuses.store', $event->id) !!}" method="POST" id="bonusCreate" class="seamless">
							@csrf

							<div class="d-flex flex-column flex-md-row gap-3 gap-md-4 align-items-md-center">
								<div class="flex-grow-1">
									<div class="input-group" id="bonusStart" data-td-target-input="nearest" data-td-target-toggle="nearest">
										<input id="bonusStartIpt" type="text" name="start" class="form-control" data-td-target="#bonusStart" placeholder="Start" required aria-label="Start" />
										<span class="input-group-text" data-td-target="#bonusStart" data-td-toggle="datetimepicker">
											<i class="fa-solid fa-calendar"></i>
										</span>
									</div>
								</div>

								<div class="flex-grow-1">
									<div class="input-group" id="bonusStop" data-td-target-input="nearest" data-td-target-toggle="nearest">
										<input id="bonusStopIpt" type="text" name="stop" class="form-control" data-td-target="#bonusStop" placeholder="Stop" required aria-label="Stop" />
										<span class="input-group-text" data-td-target="#bonusStop" data-td-toggle="datetimepicker">
											<i class="fa-solid fa-calendar"></i>
										</span>
									</div>
								</div>

								<div>
									<input type="number"
										min="1"
										max="10"
										step="0.25"
										required
										name="modifier"
										value="2"
										id="bonusModifier"
										class="form-control"
										style="width: 5em;"
										aria-label="Modifier" />
								</div>

								<div class="flex-grow-1">
									<select name="departments[]" id="bonusDepartments" class="form-select" multiple required aria-label="Departments">
										@foreach($departments as $department)
											<option value="{!! $department->id !!}">
												{{ $department->name }} {!! $department->hidden ? '(hidden)' : '' !!}
											</option>
										@endforeach
									</select>
								</div>

								<div>
									<button type="submit" class="btn btn-success">Create</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			@else
				<div class="alert alert-info mb-0" role="alert">Please select an event to manage the time bonuses for.</div>
			@endif
		</div>
	</div>
@endsection

@push('modules')
	@vite('resources/js/seamless-forms.js')
	@vite('resources/js/admin/bonuses.js')
@endpush
