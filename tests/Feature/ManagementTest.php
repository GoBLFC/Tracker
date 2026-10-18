<?php

use App\Models\Department;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->withoutVite();
	$this->event = activateEvent();
	$this->department = Department::factory()->create(['event_id' => $this->event->id]);
	$this->admin = userWithRole(Role::Admin);
	$this->manager = userWithRole(Role::Manager);
});

it('only lets managers into the management panel', function () {
	$this->actingAs(userWithRole(Role::Lead))->get(route('management.manage'))->assertForbidden();

	$this->actingAs($this->manager)
		->get(route('management.manage'))
		->assertOk()
		->assertInertia(fn (AssertableInertia $page) => $page
			->component('ManagerDashboard')
			->where('event.id', $this->event->id)
			->where('volunteer', null));
});

it('loads ongoing entries and a volunteer\'s info in the management panel', function () {
	$volunteer = User::factory()->create();
	$entry = TimeEntry::factory()->create([
		'user_id' => $volunteer->id,
		'event_id' => $this->event->id,
		'department_id' => $this->department->id,
		'start' => now()->subHour(),
		'stop' => null,
	]);

	$this->actingAs($this->manager)
		->get(route('management.manage.volunteer', [$this->event, $volunteer]))
		->assertInertia(fn (AssertableInertia $page) => $page
			->where('volunteer.user.id', $volunteer->id)
			->loadDeferredProps(fn (AssertableInertia $reload) => $reload
				->where('ongoingEntries.0.id', $entry->id)
				->has('recentTimeActivities', 1)));
});

it('only lets admins view reports', function () {
	$this->actingAs($this->manager)->get(route('admin.reports'))->assertForbidden();
	$this->actingAs($this->manager)->get(route('admin.reports.view', 'hours'))->assertForbidden();
});

it('sends report pages to the active event', function () {
	$this->actingAs($this->admin)
		->get(route('admin.reports'))
		->assertRedirect(route('admin.event.reports', $this->event));
	$this->actingAs($this->admin)
		->get(route('admin.reports.view', 'hours'))
		->assertRedirect(route('admin.event.reports.view', [$this->event, 'hours']));
});

it('lists the reports for an event', function () {
	$this->actingAs($this->admin)
		->get(route('admin.event.reports', $this->event))
		->assertOk()
		->assertViewIs('admin.reports')
		->assertSee('Please select a report to view.');
});

it('asks for an event when there is no active event', function () {
	Setting::set('active-event', null);

	$this->actingAs($this->admin)
		->get(route('admin.reports'))
		->assertOk()
		->assertSee('Please select an event to view reports for.');
});

it('renders event reports', function (string $reportType) {
	timeEntryFor(User::factory()->create(), $this->department, now()->subDay(), 120, ['auto' => true]);

	$this->actingAs($this->admin)
		->get(route('admin.event.reports.view', [$this->event, $reportType]))
		->assertOk()
		->assertViewIs('admin.report')
		->assertViewHas('reportType', $reportType);
})->with(['hours', 'departments', 'unclocked', 'audit']);

it('renders an attendee log report from its ID', function () {
	$log = new App\Models\AttendeeLog(['name' => 'Lounge']);
	$log->event_id = $this->event->id;
	$log->save();

	$this->actingAs($this->admin)
		->get(route('admin.event.reports.view', [$this->event, 'attendee-log', 'id' => $log->id]))
		->assertOk()
		->assertViewHas('report', fn ($report) => $report->attendeeLog->is($log));
});

it('passes the extra parameter to reports', function () {
	$this->actingAs($this->admin)
		->get(route('admin.event.reports.view', [$this->event, 'hours', 'hours' => 12]))
		->assertViewHas('report', fn ($report) => $report->hours === 12);
});

it('returns 404 for unknown reports and file types', function () {
	$this->actingAs($this->admin)->get(route('admin.event.reports.view', [$this->event, 'nope']))->assertNotFound();
	$this->actingAs($this->admin)->get(route('admin.event.reports.export', [$this->event, 'nope', 'csv']))->assertNotFound();
	$this->actingAs($this->admin)->get(route('admin.event.reports.export', [$this->event, 'hours', 'exe']))->assertNotFound();
});

it('downloads reports', function (string $fileType) {
	$this->event->update(['name' => 'BLFC 2026']);
	timeEntryFor(User::factory()->create(), $this->department, now()->subDay(), 120);

	$response = $this->actingAs($this->admin)
		->get(route('admin.event.reports.export', [$this->event, 'hours', $fileType]))
		->assertOk()
		->assertDownload();

	expect($response->headers->get('content-disposition'))->toContain('blfc-2026-hours-')->toContain(".{$fileType}");
})->with(['xlsx', 'csv', 'ods', 'html']);
