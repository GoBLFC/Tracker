<div class="card mb-3">
	<h5 class="card-header">Your Functions</h5>
	<div class="card-body">
		<div class="row gx-3">
			@lead
				<div class="col-md mb-2 mb-md-0">
					<a role="button" class="btn btn-secondary d-block" href="{!! route('management.lead') !!}">Lead Panel</a>
				</div>
			@endlead
			@manager
				<div class="col-md mb-2 mb-md-0">
					<a role="button" class="btn btn-secondary d-block" href="{!! route('management.manage') !!}">Management Panel</a>
				</div>
			@endmanager
			@admin
				<div class="col-md">
					<div class="dropdown">
						<button class="btn btn-secondary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Admin Panel</button>
						<ul class="dropdown-menu dropdown-menu-end">
							<li><a class="dropdown-item" href="{!! route('admin.site') !!}">Site</a></li>
							<li><a class="dropdown-item" href="{!! route('admin.users') !!}">Users</a></li>
							<li><a class="dropdown-item" href="{!! route('admin.departments') !!}">Departments</a></li>
							<li><a class="dropdown-item" href="{!! route('admin.events') !!}">Events</a></li>
							<li><a class="dropdown-item" href="{!! route('admin.bonuses') !!}">Bonuses</a></li>
							<li><a class="dropdown-item" href="{!! route('admin.rewards') !!}">Rewards</a></li>
							<li><a class="dropdown-item" href="{!! route('admin.reports') !!}">Reports</a></li>
						</ul>
					</div>
				</div>
			@endadmin
		</div>
	</div>
</div>
