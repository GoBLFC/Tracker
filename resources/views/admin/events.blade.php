@extends('layouts.admin')

@section('admin-content')
	<div class="card mb-3">
		<h4 class="card-header">Event Management</h4>
		<div class="card-body">
			<p>
				Events are the overarching entity that contain all relevant information for a single convention.
				They have rewards, time bonuses, time entries, and attendee logs associated with them.<br />
				All time entries entered by volunteers and managers are automatically associated with the <a href="{!! route('settings.index') !!}">active event</a>.
			</p>
			<p>
				Deleting events does <strong>not</strong> delete their associated rewards, time bonuses, or time entries, although there is no way to view those for deleted events.<br />
				This isn't recommended unless you're certain they won't be relevant again.
			</p>

			<div class="card mb-4">
				<h5 class="card-header">Events</h5>

				@if(!$events->isEmpty())
					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table table-dark table-striped mb-0">
								<thead>
									<tr>
										<th scope="col">Name</th>
										<th scope="col"></th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody>
									@foreach($events->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE) as $event)
										<tr>
											<td class="w-100">
												<input form="update-{!! $event->id !!}"
													type="text"
													class="form-control evtName"
													name="name"
													value="{{ $event->display_name }}"
													required />
											</td>
											<td>
												<form action="{!! route('events.update', $event->id) !!}" method="POST" id="update-{!! $event->id !!}" class="seamless update">
													@method('PUT')
													@csrf
													<button type="submit" class="btn btn-success float-end" data-success="Updated event.">Save</button>
												</form>
											</td>
											<td>
												<form action="{!! route('events.destroy', $event->id) !!}" method="POST" id="delete-{!! $event->id !!}" class="seamless delete">
													@method('DELETE')
													@csrf
													<button type="submit"
														class="btn btn-danger float-end"
														data-success="Deleted event."
														data-confirm-title="Delete event?"
														data-confirm-text="{{ $event->display_name }}">

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
						<p class="mb-0">There are no events.</p>
					</div>
				@endif
			</div>

			<div class="card">
				<h5 class="card-header">Create Event</h5>
				<div class="card-body">
					<form action="{!! route('events.store') !!}" method="POST" id="evtCreate" class="seamless">
						@csrf
						<div class="input-group">
							<label for="evtName" class="input-group-text">Name</label>
							<input type="text" name="name" id="evtName" class="form-control" required />
							<button type="submit" class="btn btn-success">Create</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('modules')
	@vite('resources/js/legacy/seamless-forms.js')
	@vite('resources/js/legacy/admin/events.js')
@endpush
