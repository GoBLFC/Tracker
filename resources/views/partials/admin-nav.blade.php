<ul class="nav nav-pills justify-content-center mb-3">
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('admin.site')]) href="{!! route('admin.site') !!}">Site</a>
	</li>
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('admin.users')]) href="{!! route('admin.users') !!}">Users</a>
	</li>
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('admin.departments')]) href="{!! route('admin.departments') !!}">Departments</a>
	</li>
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('admin.events')]) href="{!! route('admin.events') !!}">Events</a>
	</li>
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('admin.bonuses', 'admin.event.bonuses')]) href="{!! route('admin.bonuses') !!}">Bonuses</a>
	</li>
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('admin.rewards', 'admin.event.rewards')]) href="{!! route('admin.rewards') !!}">Rewards</a>
	</li>
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('admin.attendee-logs', 'admin.event.attendee-logs')]) href="{!! route('admin.attendee-logs') !!}">Attendee Logs</a>
	</li>
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('admin.reports', 'admin.reports.view', 'admin.reports.export', 'admin.event.reports', 'admin.event.reports.view', 'admin.event.reports.export')]) href="{!! route('admin.reports') !!}">Reports</a>
	</li>
</ul>
