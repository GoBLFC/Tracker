@extends('layouts.admin')

@section('admin-content')
	<div class="card mb-3">
		<h4 class="card-header">Department Management</h4>
		<div class="card-body">
			<p>
				Departments are the section of your organization that deal with a specific set of tasks or goals before, during, and after your event.<br />
				Users choose what department to check in to for their shifts, and so every time entry always has one associated with it.
			</p>
			<p>
				Deleting departments does <strong>not</strong> delete their associated time entries.
			</p>

			<div class="card mb-4">
				<h5 class="card-header">Departments</h5>

				@if(!$departments->isEmpty())
					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table table-dark table-striped mb-0">
								<thead>
									<tr>
										<th scope="col">Name</th>
										<th scope="col">Hide</th>
										<th scope="col"></th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody>
									@foreach($departments->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE) as $department)
										<tr>
											<td class="w-100">
												<input form="update-{!! $department->id !!}"
													type="text"
													class="form-control dptName"
													name="name"
													value="{{ $department->display_name }}"
													required />
											</td>
											<td class="align-middle">
												<div class="form-check form-switch">
													<input form="update-{!! $department->id !!}" type="hidden" name="hidden" value="0" />
													<input form="update-{!! $department->id !!}"
														type="checkbox"
														role="switch"
														class="form-check-input dptHidden"
														name="hidden"
														value="1"
														{!! $department->hidden ? 'checked' : '' !!} />
												</div>
											</td>
											<td>
												<form action="{!! route('departments.update', $department->id) !!}" method="POST" id="update-{!! $department->id !!}" class="seamless update">
													@method('PUT')
													@csrf
													<button type="submit" class="btn btn-success float-end" data-success="Updated department.">Save</button>
												</form>
											</td>
											<td>
												<form action="{!! route('departments.destroy', $department->id) !!}" method="POST" id="delete-{!! $department->id !!}" class="seamless delete">
													@method('DELETE')
													@csrf
													<button type="submit"
														class="btn btn-danger float-end"
														data-success="Deleted department."
														data-confirm-title="Delete department?"
														data-confirm-text="{{ $department->display_name }}">

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
						<p class="mb-0">There are no departments.</p>
					</div>
				@endif
			</div>

			<div class="card">
				<h5 class="card-header">Create Department</h5>
				<div class="card-body">
					<form action="{!! route('departments.store') !!}" method="POST" id="dptCreate" class="seamless">
						@csrf
						<div class="d-flex flex-column flex-md-row gap-3 gap-md-4 align-items-md-center">
							<div class="flex-grow-1">
								<div class="input-group">
									<label for="dptName" class="input-group-text">Name</label>
									<input type="text" name="name" id="dptName" class="form-control" required />
								</div>
							</div>
							<div>
								<div class="form-check form-switch mb-0">
									<input type="hidden" name="hidden" value="0" />
									<input type="checkbox" name="hidden" value="1" id="dptHidden" class="form-check-input" role="switch"  />
									<label for="dptHidden" class="form-label mb-0">Hide</label>
								</div>
							</div>
							<div>
								<button type="submit" class="btn btn-success">Create</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('modules')
	@vite('resources/js/seamless-forms.js')
	@vite('resources/js/admin/departments.js')
@endpush
