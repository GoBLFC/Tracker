<?php

use App\Facades\ConCat;
use App\Models\AttendeeLog;
use App\Models\Department;
use App\Models\Event;
use App\Models\Role;
use App\Models\TimeEntry;
use App\Models\User;
use App\Reports\AttendeeLogReport;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;

uses(RefreshDatabase::class);

/**
 * Builds a fake ConCat registration for a badge ID
 */
function fakeRegistration(int $badgeId, string $productName, string $productId = 'prod-1'): stdClass {
	return (object) [
		'badgeName' => "Badge {$badgeId}",
		'status' => 'paid',
		'productId' => $productId,
		'productName' => $productName,
		'productDisplayName' => null,
		'user' => (object) [
			'id' => $badgeId,
			'username' => "user{$badgeId}",
			'firstName' => 'Test',
			'lastName' => 'User',
		],
	];
}

/**
 * Gets the current Inertia asset version so partial reload requests aren't rejected
 */
function inertiaVersion(): string {
	return (string) app(\App\Http\Middleware\HandleInertiaRequests::class)->version(request());
}

beforeEach(function () {
	$this->admin = User::factory()->create(['role' => Role::Admin]);
	$event = Event::factory()->create();
	$this->log = new AttendeeLog(['name' => 'Sponsor Lounge']);
	$this->log->event_id = $event->id;
	$this->log->save();
});

it('allows any registration when the log has no allowed levels', function () {
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->with(1234)->andReturn(fakeRegistration(1234, 'Attendee'));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => 1234])
		->assertOk();

	expect($this->log->attendees()->count())->toBe(1);
});

it('allows a registration whose product name matches, ignoring case', function () {
	$this->log->update(['allowed_registration_levels' => ['sponsor', 'Super Sponsor']]);
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->andReturn(fakeRegistration(1234, 'Sponsor'));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => 1234])
		->assertOk();

	expect($this->log->attendees()->count())->toBe(1);
});

it('allows a registration whose product ID matches', function () {
	$this->log->update(['allowed_registration_levels' => ['prod-sponsor']]);
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->andReturn(fakeRegistration(1234, 'Sponsor', 'prod-sponsor'));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => 1234])
		->assertOk();
});

it('denies a registration with a level that is not allowed', function () {
	$this->log->update(['allowed_registration_levels' => ['Sponsor']]);
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->andReturn(fakeRegistration(1234, 'Attendee'));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => 1234])
		->assertForbidden()
		->assertJsonPath('error', "Denied: Badge 1234 (#1234) is registered as Attendee, which isn't allowed in this log.");

	expect($this->log->attendees()->count())->toBe(0);
	expect(User::whereBadgeId(1234)->exists())->toBeFalse();
});

it('checks the level of users that already exist locally', function () {
	User::factory()->create(['badge_id' => 1234, 'role' => Role::Attendee]);
	$this->log->update(['allowed_registration_levels' => ['Sponsor']]);
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->andReturn(fakeRegistration(1234, 'Attendee'));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => 1234])
		->assertForbidden();
});

it('does not check the level of gatekeepers', function () {
	User::factory()->create(['badge_id' => 1234]);
	$this->log->update(['allowed_registration_levels' => ['Sponsor']]);
	ConCat::shouldReceive('getRegistration')->never();

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => 1234, 'type' => 'gatekeeper'])
		->assertOk();
});

it('lists distinct registration levels from ConCat and caches them', function () {
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('searchRegistrations')->once()->andReturn([
		fakeRegistration(1, 'Sponsor', 'prod-sponsor'),
		fakeRegistration(2, 'Attendee', 'prod-attendee'),
		fakeRegistration(3, 'Sponsor', 'prod-sponsor'),
	]);

	foreach ([1, 2] as $_) {
		$this->actingAs($this->admin)
			->get(route('attendee-logs.show', $this->log), [
				'X-Inertia' => 'true',
				'X-Inertia-Version' => inertiaVersion(),
				'X-Inertia-Partial-Component' => 'AttendeeLogDetails',
				'X-Inertia-Partial-Data' => 'registrationLevels',
			])
			->assertOk()
			->assertJsonPath('props.registrationLevels', [
				['id' => 'prod-attendee', 'name' => 'Attendee', 'display_name' => null],
				['id' => 'prod-sponsor', 'name' => 'Sponsor', 'display_name' => null],
			]);
	}
});

it('does not give registration levels to gatekeepers', function () {
	$gatekeeper = User::factory()->create(['role' => Role::Volunteer]);
	$this->log->users()->attach($gatekeeper, ['type' => 'gatekeeper']);
	\App\Models\Setting::set('active-event', $this->log->event_id);
	\App\Models\Setting::set('lockdown', false);
	ConCat::shouldReceive('searchRegistrations')->never();
	$headers = ['X-Inertia' => 'true', 'X-Inertia-Version' => inertiaVersion()];

	// The prop isn't offered as a deferred prop to gatekeepers, while admins are offered it
	$this->actingAs($gatekeeper)
		->get(route('attendee-logs.show', $this->log), $headers)
		->assertOk()
		->assertJsonMissingPath('deferredProps');
	$this->actingAs($this->admin)
		->get(route('attendee-logs.show', $this->log), $headers)
		->assertOk()
		->assertJsonPath('deferredProps.default', ['registrationLevels']);
});

it('only lets admins refresh the registration levels, clearing the cache', function () {
	$manager = User::factory()->create(['role' => Role::Manager]);
	Cache::put('concat:registration-levels', [['id' => 'old', 'name' => 'Old', 'display_name' => null]]);

	$this->actingAs($manager)
		->post(route('attendee-logs.registration-levels.refresh'))
		->assertForbidden();
	expect(Cache::has('concat:registration-levels'))->toBeTrue();

	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('searchRegistrations')->once()->andReturn([fakeRegistration(1, 'Sponsor', 'prod-sponsor')]);

	$this->actingAs($this->admin)
		->postJson(route('attendee-logs.registration-levels.refresh'))
		->assertOk()
		->assertJsonPath('registration_levels.0.name', 'Sponsor');
});

it('does not cache a failure to load the registration levels', function () {
	ConCat::shouldReceive('authorize')->twice();
	// ConCat fails on the first request and works on the second
	$calls = 0;
	ConCat::shouldReceive('searchRegistrations')
		->twice()
		->andReturnUsing(function () use (&$calls) {
			if (++$calls === 1) throw new RuntimeException('ConCat is down');
			return [fakeRegistration(1, 'Sponsor', 'prod-sponsor')];
		});

	$this->actingAs($this->admin)
		->postJson(route('attendee-logs.registration-levels.refresh'))
		->assertOk()
		->assertJsonPath('registration_levels', null);
	expect(Cache::has('concat:registration-levels'))->toBeFalse();

	$this->actingAs($this->admin)
		->postJson(route('attendee-logs.registration-levels.refresh'))
		->assertOk()
		->assertJsonPath('registration_levels.0.name', 'Sponsor');
});

/**
 * Gives a user a finished time entry of a specific length for an event
 */
function addHours(User $user, string $eventId, float $hours): void {
	$start = now()->subDays(1)->startOfHour();
	TimeEntry::factory()->create([
		'user_id' => $user->id,
		'event_id' => $eventId,
		'department_id' => Department::factory()->create(['event_id' => $eventId])->id,
		'start' => $start,
		'stop' => $start->avoidMutation()->addMinutes((int) round($hours * 60)),
		'auto' => false,
	]);
}

/**
 * Creates a volunteer with a finished time entry of a specific length for an event
 */
function volunteerWithHours(string $eventId, float $hours, array $attributes = []): User {
	$user = User::factory()->create(['role' => Role::Volunteer, ...$attributes]);
	addHours($user, $eventId, $hours);
	return $user;
}

it('allows staff without contacting ConCat when staff are allowed', function () {
	$staff = User::factory()->create(['role' => Role::Staff]);
	$this->log->update(['allow_staff' => true, 'min_volunteer_hours' => 12]);
	ConCat::shouldReceive('getRegistration')->never();

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $staff->badge_id])
		->assertOk();
});

it('allows volunteers with enough hours for the log\'s event', function () {
	$volunteer = volunteerWithHours($this->log->event_id, 12.5);
	$this->log->update(['allow_staff' => true, 'min_volunteer_hours' => 12]);
	ConCat::shouldReceive('getRegistration')->never();

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id])
		->assertOk();
});

it('denies volunteers without enough hours and explains why', function () {
	// Hours from other events don't count towards the log's event
	$volunteer = volunteerWithHours($this->log->event_id, 9.5, ['badge_name' => 'Foxy']);
	addHours($volunteer, Event::factory()->create()->id, 20);
	$this->log->update(['allow_staff' => true, 'min_volunteer_hours' => 12]);
	ConCat::shouldReceive('getRegistration')->never();

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id])
		->assertForbidden()
		->assertJsonPath(
			'error',
			"Denied: Foxy (#{$volunteer->badge_id}) isn't staff and has 9.5 of 12 required volunteer hours.",
		);
});

it('denies unknown badges on logs requiring staff or hours without creating them', function () {
	$this->log->update(['min_volunteer_hours' => 12]);
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->andReturn(fakeRegistration(1234, 'Attendee'));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => 1234])
		->assertForbidden()
		->assertJsonPath('error', 'Denied: Badge 1234 (#1234) has 0 of 12 required volunteer hours.');

	expect(User::whereBadgeId(1234)->exists())->toBeFalse();
});

it('allows volunteers without enough hours through an allowed registration level', function () {
	$volunteer = volunteerWithHours($this->log->event_id, 2);
	$this->log->update(['allowed_registration_levels' => ['Sponsor'], 'min_volunteer_hours' => 12]);
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->andReturn(fakeRegistration($volunteer->badge_id, 'Sponsor'));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id])
		->assertOk();
});

it('lets admins override entry requirements and records who did it', function () {
	$volunteer = volunteerWithHours($this->log->event_id, 11.5);
	$this->log->update(['min_volunteer_hours' => 12]);

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id])
		->assertForbidden()
		->assertJsonPath('can_override', true);

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id, 'override' => true])
		->assertOk()
		->assertJsonPath('overridden', true);

	expect($this->log->attendees()->first()->pivot->overridden_by_id)->toBe($this->admin->id);
});

it('records an optional reason with overrides', function () {
	$withReason = volunteerWithHours($this->log->event_id, 11.5);
	$withoutReason = volunteerWithHours($this->log->event_id, 11.5);
	$this->log->update(['min_volunteer_hours' => 12]);

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), [
			'badge_id' => $withReason->badge_id,
			'override' => true,
			'override_reason' => '  Worked setup before clocking in  ',
		])
		->assertOk();
	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), [
			'badge_id' => $withoutReason->badge_id,
			'override' => true,
			'override_reason' => '   ',
		])
		->assertOk();

	$reasons = $this->log->attendees()->get()->mapWithKeys(fn ($user) => [$user->id => $user->pivot->override_reason]);
	expect($reasons[$withReason->id])->toBe('Worked setup before clocking in')
		->and($reasons[$withoutReason->id])->toBeNull();
});

it('ignores override reasons when the attendee meets the requirements', function () {
	$volunteer = volunteerWithHours($this->log->event_id, 13);
	$this->log->update(['min_volunteer_hours' => 12]);

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), [
			'badge_id' => $volunteer->badge_id,
			'override' => true,
			'override_reason' => 'Not needed',
		])
		->assertOk();

	expect($this->log->attendees()->first()->pivot->override_reason)->toBeNull();
});

it('does not mark entries as overridden when the attendee meets the requirements', function () {
	$volunteer = volunteerWithHours($this->log->event_id, 13);
	$this->log->update(['min_volunteer_hours' => 12]);

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id, 'override' => true])
		->assertOk()
		->assertJsonPath('overridden', false);

	expect($this->log->attendees()->first()->pivot->overridden_by_id)->toBeNull();
});

it('does not let gatekeepers override entry requirements', function () {
	$gatekeeper = User::factory()->create(['role' => Role::Volunteer]);
	$this->log->users()->attach($gatekeeper, ['type' => 'gatekeeper']);
	\App\Models\Setting::set('active-event', $this->log->event_id);
	\App\Models\Setting::set('lockdown', false);
	$volunteer = volunteerWithHours($this->log->event_id, 11.5);
	$this->log->update(['min_volunteer_hours' => 12]);

	$this->actingAs($gatekeeper)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id])
		->assertForbidden()
		->assertJsonPath('can_override', false);

	$this->actingAs($gatekeeper)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id, 'override' => true])
		->assertForbidden()
		->assertJsonPath('error', 'Only managers and admins can let attendees into this log anyway.');

	// The page gets an inline error rather than an authorization error page
	$this->actingAs($gatekeeper)
		->from(route('attendee-logs.show', $this->log))
		->put(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id, 'override' => true])
		->assertRedirect(route('attendee-logs.show', $this->log))
		->assertSessionHasErrors(['requirements' => 'Only managers and admins can let attendees into this log anyway.']);

	expect($this->log->hasAttendee($volunteer))->toBeFalse();
});

it('lets gatekeepers override entry requirements when the log allows it', function () {
	$gatekeeper = User::factory()->create(['role' => Role::Volunteer]);
	$this->log->users()->attach($gatekeeper, ['type' => 'gatekeeper']);
	\App\Models\Setting::set('active-event', $this->log->event_id);
	\App\Models\Setting::set('lockdown', false);
	$volunteer = volunteerWithHours($this->log->event_id, 11.5);
	$this->log->update(['min_volunteer_hours' => 12, 'gatekeepers_can_override' => true]);

	$this->actingAs($gatekeeper)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id])
		->assertForbidden()
		->assertJsonPath('can_override', true);

	$this->actingAs($gatekeeper)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id, 'override' => true])
		->assertOk();

	expect($this->log->attendees()->first()->pivot->overridden_by_id)->toBe($gatekeeper->id);
});

it('reports overridden attendees as already present instead of denying them again', function () {
	$volunteer = volunteerWithHours($this->log->event_id, 2);
	$this->log->update(['min_volunteer_hours' => 12]);
	$this->log->users()->attach($volunteer, ['type' => 'attendee', 'overridden_by_id' => $this->admin->id]);

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id])
		->assertUnprocessable()
		->assertJsonPath('error', fn (string $error) => str_contains($error, 'already present'));
});

it('puts denials under their own error key for the frontend', function () {
	$volunteer = volunteerWithHours($this->log->event_id, 2);
	$this->log->update(['min_volunteer_hours' => 12]);

	$this->actingAs($this->admin)
		->from(route('attendee-logs.show', $this->log))
		->put(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id])
		->assertRedirect()
		->assertSessionHasErrors('requirements')
		->assertSessionDoesntHaveErrors('badge_id');
});

it('denies existing users whose level cannot be checked, allowing an override', function () {
	$volunteer = User::factory()->create(['role' => Role::Volunteer, 'badge_name' => 'Foxy']);
	$this->log->update(['allowed_registration_levels' => ['Sponsor']]);
	ConCat::shouldReceive('authorize')->twice();
	ConCat::shouldReceive('getRegistration')->twice()->andThrow(new RuntimeException('ConCat is down'));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id])
		->assertForbidden()
		->assertJsonPath(
			'error',
			"Denied: Foxy (#{$volunteer->badge_id}) couldn't have their registration level checked with ConCat.",
		);

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id, 'override' => true])
		->assertOk();
	expect($this->log->attendees()->first()->pivot->overridden_by_id)->toBe($this->admin->id);
});

it('tells existing users without a ConCat registration apart from ConCat being unreachable', function () {
	$volunteer = User::factory()->create(['role' => Role::Volunteer, 'badge_name' => 'Foxy']);
	$this->log->update(['allowed_registration_levels' => ['Sponsor']]);
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->andThrow(new ClientException(
		'Not Found',
		new GuzzleRequest('GET', '/api/v0/users/1/registration'),
		new GuzzleResponse(404),
	));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $volunteer->badge_id])
		->assertForbidden()
		->assertJsonPath('error', "Denied: Foxy (#{$volunteer->badge_id}) doesn't have a ConCat registration.");
});

it('reports unknown badges as not found when ConCat cannot be reached, even with an override', function () {
	$this->log->update(['allowed_registration_levels' => ['Sponsor']]);
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->andThrow(new RuntimeException('ConCat is down'));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => 1234, 'override' => true])
		->assertNotFound();

	expect(User::whereBadgeId(1234)->exists())->toBeFalse();
});

it('creates users from ConCat when overriding entry requirements for unknown badges', function () {
	$this->log->update(['min_volunteer_hours' => 12]);
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->andReturn(fakeRegistration(1234, 'Attendee'));

	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => 1234, 'override' => true])
		->assertOk()
		->assertJsonPath('overridden', true);

	$user = User::whereBadgeId(1234)->firstOrFail();
	expect($user->role)->toBe(Role::Attendee)
		->and($this->log->attendees()->first()->pivot->overridden_by_id)->toBe($this->admin->id);
});

it('rejects a minimum of less than 1 volunteer hour or more than 2 decimal places', function (float $hours) {
	$this->actingAs($this->admin)
		->patchJson(route('attendee-logs.update', $this->log), ['min_volunteer_hours' => $hours])
		->assertUnprocessable()
		->assertJsonValidationErrors('min_volunteer_hours');
})->with([0, -1, 0.5, 0.99, 12.505]);

it('accepts a minimum of 1 volunteer hour or more', function (float $hours) {
	$this->actingAs($this->admin)
		->patchJson(route('attendee-logs.update', $this->log), ['min_volunteer_hours' => $hours])
		->assertOk();

	expect($this->log->fresh()->min_volunteer_hours)->toBe($hours);
})->with([1.0, 12.5, 11.25]);

it('only lets admins change entry requirements', function () {
	$manager = User::factory()->create(['role' => Role::Manager]);

	$this->actingAs($manager)
		->patchJson(route('attendee-logs.update', $this->log), ['allow_staff' => true])
		->assertForbidden();

	expect($this->log->fresh()->allow_staff)->toBeFalse();
});

it('exports who overrode entry requirements and why, without evaluating formulas', function (string $writerType) {
	// The overrider's account is deleted afterwards, and should still be named
	$overrider = User::factory()->create(['role' => Role::Manager]);
	$volunteer = volunteerWithHours($this->log->event_id, 2, ['badge_name' => '@Foxy']);
	$this->log->users()->attach($volunteer, [
		'type' => 'attendee',
		'overridden_by_id' => $overrider->id,
		'override_reason' => '=1+1',
	]);
	$overrider->delete();

	$report = new AttendeeLogReport($this->log->event, $this->log->id);
	$path = tempnam(sys_get_temp_dir(), 'report') . '.' . strtolower($writerType);
	file_put_contents($path, Excel::raw($report, $writerType));
	$sheet = IOFactory::load($path)->getActiveSheet();
	unlink($path);

	expect($sheet->getCell('D1')->getValue())->toBe('Overridden By')
		->and($sheet->getCell('E1')->getValue())->toBe('Override Reason')
		->and($sheet->getCell('D2')->getValue())->toBe($overrider->display_name)
		->and($sheet->getCell('E2')->getValue())->toBe("'=1+1")
		->and($sheet->getCell('E2')->getDataType())->toBe(DataType::TYPE_STRING);
})->with([\Maatwebsite\Excel\Excel::XLSX, \Maatwebsite\Excel\Excel::CSV]);

it('escapes names that a spreadsheet would treat as formulas', function () {
	$volunteer = volunteerWithHours($this->log->event_id, 2);
	$this->log->users()->attach($volunteer, ['type' => 'attendee']);
	$report = new AttendeeLogReport($this->log->event, $this->log->id);

	foreach (['=SUM(A1)', '+1', '-1', '@Foxy', "\tTab"] as $name) {
		$volunteer->forceFill(['badge_name' => $name])->save();
		$row = $report->map($this->log->attendees()->first());
		expect($row[1])->toStartWith("'");
	}

	$volunteer->forceFill(['badge_name' => 'Foxy'])->save();
	expect($report->map($this->log->attendees()->first())[3])->toBeNull();
});

/**
 * Creates a gatekeeper for the test log and makes its event active so the gatekeeper can use it
 */
function makeGatekeeper(AttendeeLog $log): User {
	$gatekeeper = User::factory()->create(['role' => Role::Volunteer]);
	$log->users()->attach($gatekeeper, ['type' => 'gatekeeper']);
	\App\Models\Setting::set('active-event', $log->event_id);
	\App\Models\Setting::set('lockdown', false);
	return $gatekeeper;
}

it('gives the page the viewer\'s override permissions and the overriders, including deleted ones', function () {
	$gatekeeper = makeGatekeeper($this->log);
	$overrider = User::factory()->create(['role' => Role::Manager, 'badge_name' => 'Former Manager']);
	$this->log->users()->attach(volunteerWithHours($this->log->event_id, 2), [
		'type' => 'attendee',
		'overridden_by_id' => $overrider->id,
	]);
	$overrider->delete();
	$headers = ['X-Inertia' => 'true', 'X-Inertia-Version' => inertiaVersion()];

	$this->actingAs($gatekeeper)
		->get(route('attendee-logs.show', $this->log), $headers)
		->assertOk()
		->assertJsonPath('props.canOverrideRequirements', false)
		->assertJsonPath("props.overriders.{$overrider->id}.badge_name", 'Former Manager');

	$this->log->update(['gatekeepers_can_override' => true]);
	$this->actingAs($gatekeeper)
		->get(route('attendee-logs.show', $this->log), $headers)
		->assertJsonPath('props.canOverrideRequirements', true);

	$this->actingAs($this->admin)
		->get(route('attendee-logs.show', $this->log), $headers)
		->assertJsonPath('props.canOverrideRequirements', true);
});

it('lets admins set and clear the allowed levels', function () {
	$this->actingAs($this->admin)
		->patchJson(route('attendee-logs.update', $this->log), ['allowed_registration_levels' => ['Sponsor']])
		->assertOk();
	expect($this->log->fresh()->allowed_registration_levels)->toBe(['Sponsor']);

	$this->actingAs($this->admin)
		->patchJson(route('attendee-logs.update', $this->log), ['allowed_registration_levels' => null])
		->assertOk();
	expect($this->log->fresh()->allowed_registration_levels)->toBeNull();
});
