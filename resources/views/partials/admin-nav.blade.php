<ul class="nav nav-pills justify-content-center mb-3">
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('settings.index')]) href="{!! route('settings.index') !!}">Site</a>
	</li>
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('users.index')]) href="{!! route('users.index') !!}">Users</a>
	</li>
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('events.index')]) href="{!! route('events.index') !!}">Events</a>
	</li>
	<li class="nav-item">
		<a @class(['nav-link', 'active' => Route::is('admin.reports', 'admin.reports.view', 'admin.reports.export', 'admin.event.reports', 'admin.event.reports.view', 'admin.event.reports.export')]) href="{!! route('admin.reports') !!}">Reports</a>
	</li>
</ul>
