<?php

use App\Facades\ConCat;
use App\Models\Activity;
use App\Models\Department;
use App\Models\Role;
use App\Models\TimeBonus;
use App\Models\User;
use App\Reports\AuditLogReport;
use App\Reports\AutoClosedTimeEntriesReport;
use App\Reports\DepartmentSummaryReport;
use App\Reports\VolunteerApplicationDepartmentSummaryReport;
use App\Reports\VolunteerApplicationsReport;
use App\Reports\VolunteerTimeReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->event = activateEvent(App\Models\Event::factory()->create(['name' => 'BLFC 2026']));
	$this->security = Department::factory()->visible()->create(['event_id' => $this->event->id, 'name' => 'Security']);
	$this->registration = Department::factory()->create(['event_id' => $this->event->id, 'name' => 'Registration']);
	$this->foxy = User::factory()->create(['badge_id' => 1, 'username' => 'foxy']);
	$this->wolfy = User::factory()->create(['badge_id' => 2, 'username' => 'wolfy']);
	$start = now()->subDays(2)->startOfHour();
	timeEntryFor($this->foxy, $this->security, $start, 120);
	timeEntryFor($this->foxy, $this->registration, $start->avoidMutation()->addHours(3), 60);
	timeEntryFor($this->wolfy, $this->security, $start, 30, ['auto' => true]);
	// Time for other events isn't counted
	timeEntryFor($this->wolfy, Department::factory()->create(), $start, 600);

	$bonus = TimeBonus::factory()->create([
		'event_id' => $this->event->id,
		'start' => $start,
		'stop' => $start->avoidMutation()->addHour(),
		'modifier' => 2,
	]);
	$bonus->departments()->attach($this->security);
});

/**
 * Builds a fake ConCat volunteer application
 */
function fakeVolunteer(int $id, array $departments, int $hours, array $extra = []): stdClass {
	return json_decode(json_encode([
		'user' => [
			'id' => $id,
			'username' => "user{$id}",
			'preferredName' => "Pref {$id}",
			'firstName' => 'First',
			'lastName' => "Last{$id}",
			'isStaff' => false,
			'email' => "user{$id}@example.com",
			'phone' => '555-0100',
		],
		'departments' => $departments,
		'contactMethods' => [['name' => 'telegram', 'value' => "@user{$id}", 'isPrimary' => true]],
		'options' => [
			['name' => VolunteerApplicationDepartmentSummaryReport::AVAILABLE_HOURS_OPTION, 'value' => $hours],
			['name' => 'Volunteer Days', 'value' => ['Friday', 'Saturday']],
			['name' => 'Have you previously volunteered with BLFC 2026? If so, what did you do? [Optional]', 'value' => 'Lots'],
		],
		'createdAt' => '2026-01-01T00:00:00Z',
		'updatedAt' => '2026-01-02T00:00:00Z',
		...$extra,
	]));
}

/**
 * Builds a fake department entry of a ConCat volunteer application
 */
function fakeApplicationDepartment(int $id, string $name, array $states): array {
	return ['id' => $id, 'name' => $name, 'states' => $states];
}

it('reports hours worked and earned per volunteer for the event', function () {
	$data = (new VolunteerTimeReport($this->event, 0))->toCollection();

	expect($data['head']->all())->toBe(['Badge Number', 'Username', 'Hours Worked', 'Hours Earned', 'Departments'])
		->and($data['body']->values()->all())->toEqual([
			[1, 'foxy', 3.0, 4.0, 'Registration, Security'],
			[2, 'wolfy', 0.5, 1.0, 'Security'],
		])
		->and($data['totals']->all())->toEqual(['Totals', null, 3.5, 5.0, null]);
});

it('only reports volunteers above the hour threshold', function () {
	$data = (new VolunteerTimeReport($this->event, 2))->toCollection();

	expect($data['body']->pluck(0)->values()->all())->toBe([1]);
});

it('summarizes hours, volunteers, and entries per department', function () {
	$data = (new DepartmentSummaryReport($this->event))->toCollection();

	expect($data['body']->all())->toEqual([
		['Registration', 1.0, 1, 1],
		['Security', 2.5, 2, 2],
	])
		->and($data['totals']->all())->toEqual(['Totals', 3.5, 2, 3, null]);
});

it('lists automatically stopped time entries', function () {
	$report = new AutoClosedTimeEntriesReport($this->event);
	$rows = $report->toCollection()['body'];

	expect($rows)->toHaveCount(1)
		->and($rows[0][0])->toBe(2)
		->and($rows[0][4])->toBe('Security')
		->and($report->filename('csv'))->toStartWith('blfc-2026-unclocked-')->toEndWith('.csv')
		->and($report->properties())->toMatchArray(['title' => 'Unclocked Users', 'description' => 'BLFC 2026 unclocked users']);
});

it('reports changes made by users to other entities', function () {
	$admin = userWithRole(Role::Admin, ['badge_name' => 'Admin', 'badge_id' => 99]);
	Activity::query()->delete();
	$this->actingAs($admin);
	$this->security->update(['name' => 'Safety', 'hidden' => true]);

	// Volunteers changing their own time entries aren't reported
	$this->actingAs($this->foxy);
	$this->foxy->timeEntries()->first()->update(['notes' => 'Changed']);

	$rows = (new AuditLogReport(0))->toCollection()['body']->values();

	expect($rows)->toHaveCount(1)
		->and($rows[0][1])->toBe('Admin (#99)')
		->and($rows[0][2])->toBe('Department')
		->and($rows[0][3])->toBe('Safety')
		->and($rows[0][4])->toBe('- Event: BLFC 2026')
		->and($rows[0][5])->toBe('updated')
		->and($rows[0][6])->toContain('- name: Security → Safety')
		->toContain('- hidden: false → true');
});

it('limits the audit log to recent days', function () {
	$admin = userWithRole(Role::Admin);
	Activity::query()->delete();
	$this->actingAs($admin);
	$this->security->update(['name' => 'Safety']);
	Activity::query()->update(['created_at' => now()->subDays(10)]);

	expect((new AuditLogReport(7))->toCollection()['body'])->toHaveCount(0)
		->and((new AuditLogReport(30))->toCollection()['body'])->toHaveCount(1);
});

it('reports volunteer applications from ConCat', function () {
	ConCat::shouldReceive('isAuthorized')->andReturn(false);
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('searchVolunteers')->once()->andReturn([
		fakeVolunteer(10, [fakeApplicationDepartment(1, 'Security', ['assignment', 'interest'])], 20),
		fakeVolunteer(11, [fakeApplicationDepartment(2, 'Art Show', ['avoid'])], 8),
	]);
	ConCat::shouldReceive('searchRegistrationsByUserIds')->once()->with(['10', '11'])->andReturn([
		(object) ['badgeName' => 'Foxy', 'user' => (object) ['id' => 10]],
	]);

	$report = new VolunteerApplicationsReport($this->event);
	$report->prefetch();
	$data = $report->toCollection();
	$foxy = $data['body'][10];

	expect($data['head']->slice(-2)->values()->all())->toBe(['Art Show', 'Security'])
		->and($foxy[1])->toBe('Foxy')
		->and($foxy[4])->toBe('First Last10')
		->and($foxy[6])->toBe('Security')
		->and($foxy[7])->toBe('telegram')
		->and($foxy[10])->toBe('@user10')
		->and($foxy[16])->toBe('20')
		->and(array_slice($foxy, 17, 7))->toBe([null, null, 'F', 'Sa', null, null, null])
		->and($foxy[26])->toBe('Lots')
		->and(array_slice($foxy, -2))->toBe([null, '✔️❤️'])
		->and($data['body'][11][1])->toBe('user11')
		->and(array_slice($data['body'][11], -2))->toBe(['❌', null]);
});

it('summarizes volunteer applications per department, splitting hours', function () {
	ConCat::shouldReceive('isAuthorized')->andReturn(true);
	ConCat::shouldReceive('searchVolunteers')->once()->andReturn([
		// Assigned to two departments, so only they count and get half the hours each
		fakeVolunteer(10, [
			fakeApplicationDepartment(1, 'Security', ['assignment']),
			fakeApplicationDepartment(2, 'Art Show', ['assignment']),
			fakeApplicationDepartment(3, 'Dance', ['interest']),
		], 20),
		// Unassigned, so departments they're interested in count, except ones to avoid
		fakeVolunteer(11, [
			fakeApplicationDepartment(1, 'Security', ['interest']),
			fakeApplicationDepartment(3, 'Dance', ['experience']),
			fakeApplicationDepartment(2, 'Art Show', ['avoid']),
		], 8),
	]);

	$data = (new VolunteerApplicationDepartmentSummaryReport)->toCollection();

	expect($data['body']->map(fn ($row) => array_values($row))->all())->toEqual([
		['Art Show', 1, 10.0, 0, 0.0, 1, 10.0],
		['Dance', 0, 0.0, 1, 4.0, 1, 4.0],
		['Security', 1, 10.0, 1, 4.0, 2, 14.0],
	])
		->and($data['totals']->all())->toEqual(['Totals', 2, 20.0, 2, 8.0, 4, 28.0]);
});

it('handles having no volunteer applications', function () {
	ConCat::shouldReceive('isAuthorized')->andReturn(true);
	ConCat::shouldReceive('searchVolunteers')->twice()->andReturn([]);

	expect((new VolunteerApplicationsReport($this->event))->toCollection()['body'])->toBeEmpty()
		->and((new VolunteerApplicationDepartmentSummaryReport)->toCollection()['body'])->toBeEmpty();
});
