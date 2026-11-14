<?php

use App\Facades\ConCat;
use App\Models\AttendeeLog;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->withoutVite();
	$this->event = activateEvent();
	$this->admin = userWithRole(Role::Admin);
	$this->manager = userWithRole(Role::Manager);
	$this->log = new AttendeeLog(['name' => 'Sponsor Lounge']);
	$this->log->event_id = $this->event->id;
	$this->log->save();
	$this->gatekeeper = User::factory()->create();
	$this->log->users()->attach($this->gatekeeper, ['type' => 'gatekeeper']);
});

/**
 * Creates an attendee log for an event
 */
function attendeeLogFor(Event $event, string $name): AttendeeLog {
	$log = new AttendeeLog(['name' => $name]);
	$log->event_id = $event->id;
	$log->save();
	return $log;
}

it('lists every log of the active event for managers with counts', function () {
	attendeeLogFor($this->event, 'Dance');
	attendeeLogFor(Event::factory()->create(), 'Old Lounge');

	$this->actingAs($this->manager)
		->getJson(route('attendee-logs.index'))
		->assertOk()
		->assertJsonCount(2, 'attendee_logs')
		->assertJsonPath(
			'attendee_logs',
			fn (array $logs) => collect($logs)->firstWhere('id', $this->log->id)['gatekeepers_count'] === 1,
		);
});

it('lists only the logs gatekeepers keep', function () {
	attendeeLogFor($this->event, 'Dance');

	$this->actingAs($this->gatekeeper)
		->getJson(route('attendee-logs.index'))
		->assertOk()
		->assertJsonCount(1, 'attendee_logs')
		->assertJsonPath('attendee_logs.0.id', $this->log->id);
});

it('does not list logs for other volunteers or for events that are not active', function () {
	$this->actingAs(User::factory()->create())->getJson(route('attendee-logs.index'))->assertForbidden();

	$old = Event::factory()->create();
	attendeeLogFor($old, 'Old Lounge')->users()->attach($this->gatekeeper, ['type' => 'gatekeeper']);
	$this->actingAs($this->gatekeeper)->getJson(route('events.attendee-logs.index', $old))->assertForbidden();
	$this->actingAs($this->manager)
		->getJson(route('events.attendee-logs.index', $old))
		->assertOk()
		->assertJsonCount(1, 'attendee_logs');
});

it('renders the attendee log index page', function () {
	$this->actingAs($this->manager)
		->get(route('attendee-logs.index'))
		->assertOk()
		->assertInertia(fn (AssertableInertia $page) => $page
			->component('AttendeeLogIndex')
			->has('attendeeLogs', 1)
			->has('events', 1));

	$this->actingAs($this->gatekeeper)
		->get(route('attendee-logs.index'))
		->assertInertia(fn (AssertableInertia $page) => $page->where('events', null));
});

it('shows logs to their gatekeepers only while the event is active', function () {
	$this->actingAs($this->gatekeeper)
		->getJson(route('attendee-logs.show', $this->log))
		->assertOk()
		->assertJsonPath('attendee_log.name', 'Sponsor Lounge');
	$this->actingAs(User::factory()->create())->getJson(route('attendee-logs.show', $this->log))->assertForbidden();

	App\Models\Setting::set('active-event', null);
	$this->actingAs($this->gatekeeper)->getJson(route('attendee-logs.show', $this->log))->assertForbidden();
	$this->actingAs($this->manager)->getJson(route('attendee-logs.show', $this->log))->assertOk();
});

it('lets admins create logs with names unique to the event', function () {
	$this->actingAs($this->admin)
		->postJson(route('events.attendee-logs.store', $this->event), [
			'name' => 'Dance',
			'allow_staff' => true,
			'min_volunteer_hours' => 12,
		])
		->assertOk()
		->assertJsonPath('attendee_log.event_id', $this->event->id);

	$log = AttendeeLog::whereName('Dance')->sole();
	expect($log->allow_staff)->toBeTrue()
		->and($log->min_volunteer_hours)->toBe(12.0)
		->and($log->hasEntryRequirements())->toBeTrue();

	$this->actingAs($this->admin)
		->postJson(route('events.attendee-logs.store', $this->event), ['name' => 'Sponsor Lounge'])
		->assertUnprocessable()
		->assertJsonValidationErrors('name');
});

it('rejects duplicate allowed registration levels, ignoring case', function () {
	$this->actingAs($this->admin)
		->postJson(route('events.attendee-logs.store', $this->event), [
			'name' => 'Dance',
			'allowed_registration_levels' => ['Sponsor', 'sponsor'],
		])
		->assertUnprocessable()
		->assertJsonValidationErrors('allowed_registration_levels.1');
});

it('only lets admins create and delete logs', function () {
	$this->actingAs($this->manager)
		->postJson(route('events.attendee-logs.store', $this->event), ['name' => 'Dance'])
		->assertForbidden();
	$this->actingAs($this->manager)->deleteJson(route('attendee-logs.destroy', $this->log))->assertForbidden();

	$this->actingAs($this->admin)->deleteJson(route('attendee-logs.destroy', $this->log))->assertStatus(205);
	expect($this->log->fresh()->trashed())->toBeTrue()
		->and($this->log->fresh()->display_name)->toBe('Sponsor Lounge (del)');
});

it('lets managers add gatekeepers, but not gatekeepers', function () {
	$newGatekeeper = User::factory()->create();

	$this->actingAs($this->gatekeeper)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $newGatekeeper->badge_id, 'type' => 'gatekeeper'])
		->assertForbidden();

	$this->actingAs($this->manager)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $newGatekeeper->badge_id, 'type' => 'gatekeeper'])
		->assertOk()
		->assertJsonPath('type', 'gatekeeper');

	expect($this->log->hasGatekeeper($newGatekeeper))->toBeTrue()
		->and($newGatekeeper->isGatekeeper())->toBeTrue();
});

it('only lets admins manage gatekeepers of logs for other events', function () {
	$oldLog = attendeeLogFor(Event::factory()->create(), 'Old Lounge');
	$user = User::factory()->create();

	$this->actingAs($this->manager)
		->putJson(route('attendee-logs.users.store', $oldLog), ['badge_id' => $user->badge_id, 'type' => 'gatekeeper'])
		->assertForbidden();
	$this->actingAs($this->admin)
		->putJson(route('attendee-logs.users.store', $oldLog), ['badge_id' => $user->badge_id, 'type' => 'gatekeeper'])
		->assertOk();
});

it('lets gatekeepers add attendees to logs without requirements, creating them from ConCat', function () {
	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('getRegistration')->once()->with(1234)->andReturn((object) [
		'badgeName' => 'Foxy',
		'user' => (object) ['id' => 1234, 'username' => 'foxy', 'firstName' => 'Fox', 'lastName' => 'Face'],
	]);

	$this->actingAs($this->gatekeeper)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => 1234])
		->assertOk()
		->assertJsonPath('user.badge_name', 'Foxy')
		->assertJsonPath('type', 'attendee')
		->assertJsonPath('overridden', false);

	// Attendees created this way don't have their real names stored
	$user = User::whereBadgeId(1234)->sole();
	expect($user->role)->toBe(Role::Attendee)
		->and($user->first_name)->toBe('Unidentified')
		->and($user->last_name)->toBe('Attendee')
		->and($this->log->hasAttendee($user))->toBeTrue();
});

it('does not add existing attendees twice', function () {
	$attendee = User::factory()->create(['badge_name' => 'Foxy']);
	$this->log->users()->attach($attendee, ['type' => 'attendee']);

	$this->actingAs($this->gatekeeper)
		->putJson(route('attendee-logs.users.store', $this->log), ['badge_id' => $attendee->badge_id])
		->assertUnprocessable()
		->assertJsonPath('error', "Attendee Foxy (#{$attendee->badge_id}) is already present in the log.");
});

it('validates badge IDs and entry types', function (array $data, string $field) {
	$this->actingAs($this->gatekeeper)
		->putJson(route('attendee-logs.users.store', $this->log), $data)
		->assertUnprocessable()
		->assertJsonValidationErrors($field);
})->with([
	[['badge_id' => 'abc'], 'badge_id'],
	[['badge_id' => 123456789], 'badge_id'],
	[['badge_id' => 1234, 'type' => 'bouncer'], 'type'],
]);

it('removes attendees and gatekeepers from logs', function () {
	$attendee = User::factory()->create();
	$this->log->users()->attach($attendee, ['type' => 'attendee']);

	$this->actingAs($this->gatekeeper)
		->deleteJson(route('attendee-logs.users.destroy', [$this->log, 'attendee', $attendee]))
		->assertStatus(205);
	expect($this->log->hasAttendee($attendee))->toBeFalse();

	$this->actingAs($this->gatekeeper)
		->deleteJson(route('attendee-logs.users.destroy', [$this->log, 'gatekeeper', $this->gatekeeper]))
		->assertForbidden();
	$this->actingAs($this->manager)
		->deleteJson(route('attendee-logs.users.destroy', [$this->log, 'gatekeeper', $this->gatekeeper]))
		->assertStatus(205);
	expect($this->log->hasGatekeeper($this->gatekeeper))->toBeFalse();
});

it('reports users that are not in the log when removing them', function () {
	$this->actingAs($this->manager)
		->deleteJson(route('attendee-logs.users.destroy', [$this->log, 'attendee', User::factory()->create()]))
		->assertNotFound();
});

it('checks registrations against allowed levels by product ID or name, ignoring case and spaces', function () {
	$this->log->allowed_registration_levels = [' sponsor ', 'prod-42'];

	expect($this->log->allowsRegistration((object) ['productId' => 'x', 'productName' => 'SPONSOR']))->toBeTrue()
		->and($this->log->allowsRegistration((object) ['productId' => 'PROD-42', 'productName' => 'Other']))->toBeTrue()
		->and($this->log->allowsRegistration((object) ['productId' => 'x', 'productName' => 'Attendee']))->toBeFalse()
		->and($this->log->allowsRegistration((object) []))->toBeFalse();

	$this->log->allowed_registration_levels = [];
	expect($this->log->isRestrictedByRegistrationLevel())->toBeFalse()
		->and($this->log->allowsRegistration((object) []))->toBeTrue()
		->and($this->log->hasEntryRequirements())->toBeFalse();
});
