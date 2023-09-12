@extends('layouts.admin')

@section('admin-content')
	<div class="card mb-3">
		<h4 class="card-header">Reward Management</h4>
		<div class="card-body">
			<p>
				Rewards are milestones for volunteer hours during an event.
				Volunteers are automatically notified when they reach the appropriate number of hours for a reward, and managers can claim rewards for each volunteer.
			</p>

			@include('partials.event-selector', ['route' => route('admin.event.rewards', 'event-id')])

			@if($event)
				<div class="card mb-4">
					<h5 class="card-header">Rewards</h5>

					@if(!$rewards->isEmpty())
						<div class="card-body p-0">
							<div class="table-responsive">
								<table class="table table-dark table-striped mb-0">
									<thead>
										<tr>
											<th scope="col">Name</th>
											<th scope="col">Description</th>
											<th scope="col">Hours</th>
											<th scope="col"></th>
											<th scope="col"></th>
										</tr>
									</thead>
									<tbody class="align-middle">
										@foreach($rewards->sortBy('hours', SORT_NATURAL) as $reward)
											<tr>
												<td class="w-50">
													<input form="update-{!! $reward->id !!}"
														type="text"
														class="form-control rewardName"
														name="name"
														value="{{ $reward->display_name }}"
														required />
												</td>
												<td class="w-50">
													<textarea form="update-{!! $reward->id !!}"
														name="description"
														class="form-control"
														required>{{ $reward->description }}</textarea>
												</td>
												<td>
													<input form="update-{!! $reward->id !!}"
														type="number"
														name="hours"
														min="0"
														max="{!! 24 * 7 !!}"
														value="{!! $reward->hours !!}"
														class="form-control"
														style="width: 5em;"
														required />
												</td>
												<td>
													<form action="{!! route('rewards.update', $reward->id) !!}" method="POST" id="update-{!! $reward->id !!}" class="seamless update">
														@method('PUT')
														@csrf
														<button type="submit" class="btn btn-success float-end" data-success="Updated reward.">Save</button>
													</form>
												</td>
												<td>
													<form action="{!! route('rewards.destroy', $reward->id) !!}" method="POST" id="delete-{!! $reward->id !!}" class="seamless delete">
														@method('DELETE')
														@csrf
														<button type="submit"
															class="btn btn-danger float-end"
															data-success="Deleted reward."
															data-confirm-title="Delete reward?"
															data-confirm-text="{{ $reward->display_name }}">

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
							<p class="mb-0">There are no rewards.</p>
						</div>
					@endif
				</div>

				<div class="card">
					<h5 class="card-header">Create Reward</h5>
					<div class="card-body">
						<form action="{!! route('events.rewards.store', $event->id) !!}" method="POST" id="rewardCreate" class="seamless">
							@csrf
							<div class="d-flex flex-column flex-md-row gap-3 gap-md-4 align-items-md-center">
								<div class="flex-grow-1">
									<div class="input-group">
										<label for="rewardName" class="input-group-text">Name</label>
										<input type="text" name="name" id="rewardName" class="form-control" required />
									</div>
								</div>
								<div class="flex-grow-1">
									<div class="input-group">
										<label for="rewardDescription" class="input-group-text">Description</label>
										<textarea name="description" id="rewardDescription" class="form-control" required></textarea>
									</div>
								</div>
								<div>
									<div class="input-group">
										<label for="rewardHours" class="input-group-text">Hours</label>
										<input type="number" name="hours" min="0" max="{!! 24 * 7 !!}" id="rewardHours" class="form-control" required />
									</div>
								</div>
								<div>
									<button type="submit" class="btn btn-success">Create</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			@else
				<div class="alert alert-info mb-0" role="alert">Please select an event to manage the rewards for.</div>
			@endif
		</div>
	</div>
@endsection

@push('modules')
	@vite('resources/js/seamless-forms.js')
	@vite('resources/js/admin/rewards.js')
@endpush
