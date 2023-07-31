<div class="card mb-3">
	<h5 class="card-header">Your Functions</h5>
	<div class="card-body">
		<div class="row gx-3">
			@if($user->isLead())
				<div class="col-md">
					<a role="button" class="btn btn-secondary d-block" href="/lead.php">Lead Panel</a>
				</div>
			@endif
			@if($user->isManager())
				<div class="col-md">
					<a role="button" class="btn btn-secondary d-block" href="/manage.php">Management Panel</a>
				</div>
			@endif
			@if($user->isAdmin())
				<div class="col-md">
					<div class="dropdown">
						<button class="btn btn-secondary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Admin Panel</button>
						<ul class="dropdown-menu dropdown-menu-end">
							<li><a class="dropdown-item" href="/admin.php">Site</a></li>
							<li><a class="dropdown-item" href="/admin.php?page=users">Users</a></li>
							<li><a class="dropdown-item" href="/admin.php?page=departments">Departments</a></li>
							<li><a class="dropdown-item" href="/admin.php?page=bonuses">Bonuses</a></li>
							<li><a class="dropdown-item" href="/admin.php?page=rewards">Rewards</a></li>
							<li><a class="dropdown-item" href="/admin.php?page=reports">Reports</a></li>
						</ul>
					</div>
				</div>
			@endif
		</div>
	</div>
</div>
