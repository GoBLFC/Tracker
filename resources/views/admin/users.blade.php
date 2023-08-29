@extends('layouts.admin')

@section('admin-content')
	<div class="card mb-3">
		<h4 class="card-header">User Roles</h4>
		<div class="card-body">
			<div class="row gx-lg-3 mb-3">
				@foreach(\App\Models\Role::cases() as $role)
					@if($role->value === \App\Models\Role::Volunteer->value) @continue @endif
					@php($roleUsers = $users->filter(fn($user) => $user->isRole($role, true)))

					<div class="col-sm-12 col-lg-6">
						<div class="card mb-3">
							<h5 class="card-header">{{ $role->name }}</h5>

							<div class="card-body p-0 {!! $roleUsers->isEmpty() ? 'd-none' : '' !!}">
								<div class="table-responsive">
									<table class="table table-striped mb-0" data-role="{!! $role->value !!}">
										<thead>
											<tr>
												<th scope="col">ID</th>
												<th scope="col">Username</th>
												<th scope="col">Real Name</th>
												<th scope="col"></th>
											</tr>
										</thead>
										<tbody id="uRowAdmin">
											@foreach($roleUsers as $user)
												<tr data-user-id="{!! $user->id !!}">
													<th scope="row">{!! $user->badge_id !!}</th>
													<td>{{ $user->username }}</td>
													<td>{{ $user->getRealName() }}</td>
													<td>
														<button type="button" class="btn btn-sm btn-danger float-end" data-role="0">Remove</button>
													</td>
												</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>

							<div class="card-body placeholder {!! !$roleUsers->isEmpty() ? 'd-none' : '' !!}">
								<p class="mb-0">There aren't any {{ Str::lower($role->name) }} users.</p>
							</div>
						</div>
					</div>
				@endforeach
			</div>

			<div class="card mb-3">
				<h5 class="card-header">Promote User</h5>
				<div class="card-body row gx-md-3 gy-2">
					<div class="input-group">
						<input type="text" inputmode="numeric" pattern="[0-9]+" id="badgeNumber" class="form-control" placeholder="Badge Number" aria-label="Badge Number" />
						@foreach(\App\Models\Role::cases() as $role)
							@if($role->value === \App\Models\Role::Volunteer->value) @continue @endif
							<button type="button" class="btn btn-{!! $role->colorClass() !!} d-none d-md-block" data-role="{!! $role->value !!}" disabled>
								{{ $role->actionLabel() }}
							</button>
						@endforeach
					</div>

					<div class="btn-group d-md-none">
						@foreach(\App\Models\Role::cases() as $role)
							@if($role->value === \App\Models\Role::Volunteer->value) @continue @endif
							<button type="button" class="btn btn-{!! $role->colorClass() !!}" data-role="{!! $role->value !!}" disabled>
								{{ $role->actionLabel() }}
							</button>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('modules')
	@vite('resources/js/admin/users.js')
@endpush

@push('scripts')
	<script type="text/javascript">
		const userSearchUrl = '{!! route('user.search') !!}';
		const userPatchUrl = '{!! route('user.patch', 'id') !!}';
		const roles = {{ Js::from(array_combine(
			array_column(\App\Models\Role::cases(), 'value'),
			array_column(\App\Models\Role::cases(), 'name'),
		)) }};
	</script>
@endpush
