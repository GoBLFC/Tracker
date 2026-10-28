<?php

use App\Models\Department;
use App\Models\Event;
use App\Models\Kiosk;
use App\Models\Reward;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Models\User;
use App\Notifications\RewardClaimed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->withoutVite();
	$this->event = activateEvent();
	$this->department = Department::factory()->visible()->create(['event_id' => $this->event->id]);
	$this->volunteer = User::factory()->telegramUnlinked()->create();
	fakeTelegram()->shouldReceive('getMe')->andReturn((object) ['username' => 'TrackerBot']);
});

it('shows the volunteer home with only visible departments for volunteers', function () {
	$hidden = Department::factory()->hidden()->create(['event_id' => $this->event->id]);

	$this->actingAs($this->volunteer)
		->get(route('volunteer.index'))
		->assertOk()
		->assertInertia(fn (AssertableInertia $page) => $page
			->component('VolunteerHome')
			->has('departments', 1)
			->where('departments.0.id', $this->department->id)
			->where('telegramSetupUrl', "https://t.me/TrackerBot?start={$this->volunteer->tg_setup_key}")
			->where('volunteer.user.id', $this->volunteer->id));

	$this->actingAs(userWithRole(Role::Manager))
		->get(route('volunteer.index'))
		->assertInertia(fn (AssertableInertia $page) => $page->has('departments', 2));
});

it('sends volunteers with unread notifications to their notifications first', function () {
	$this->volunteer->notify(new RewardClaimed(Reward::factory()->create(['event_id' => $this->event->id])));

	$this->actingAs($this->volunteer)
		->get(route('volunteer.index'))
		->assertRedirect(route('notifications.index'));
});

it('sends volunteers to the lockdown notice while the site is locked down, but not managers', function () {
	Setting::set('lockdown', true);

	$this->actingAs($this->volunteer)->get(route('volunteer.index'))->assertRedirect(route('volunteer.lockdown'));
	$this->actingAs($this->volunteer)
		->get(route('volunteer.lockdown'))
		->assertOk()
		->assertInertia(fn (AssertableInertia $page) => $page->component('Maintenance'));

	$this->actingAs(userWithRole(Role::Manager))->get(route('volunteer.index'))->assertOk();
});

it('sends users away from the lockdown notice when the site is not locked down', function () {
	$this->actingAs($this->volunteer)->get(route('volunteer.lockdown'))->assertRedirect(route('volunteer.index'));
});

it('lets volunteers check in at a kiosk', function () {
	$kiosk = Kiosk::create();

	$this->actingAs($this->volunteer)
		->withCredentials()
		->withCookie('kiosk', $kiosk->session_key)
		->postJson(route('volunteer.time.checkin', $this->event), ['department_id' => $this->department->id])
		->assertOk()
		->assertJsonPath('time_entry.department_id', $this->department->id);

	$entry = $this->volunteer->timeEntries()->sole();
	expect($entry->isOngoing())->toBeTrue()
		->and($entry->creator_user_id)->toBe($this->volunteer->id)
		->and($entry->event_id)->toBe($this->event->id);
});

it('does not let volunteers check in outside of a kiosk, but lets staff', function () {
	$this->actingAs($this->volunteer)
		->postJson(route('volunteer.time.checkin', $this->event), ['department_id' => $this->department->id])
		->assertForbidden();

	$staff = userWithRole(Role::Staff);
	$this->actingAs($staff)
		->postJson(route('volunteer.time.checkin', $this->event), ['department_id' => $this->department->id])
		->assertOk();
});

it('lets volunteers check in anywhere in dev mode', function () {
	Setting::set('dev-mode', true);

	$this->actingAs($this->volunteer)
		->postJson(route('volunteer.time.checkin', $this->event), ['department_id' => $this->department->id])
		->assertOk();
});

it('does not allow checking in for an event that is not active', function () {
	$other = Event::factory()->create();
	$department = Department::factory()->create(['event_id' => $other->id]);

	$this->actingAs(userWithRole(Role::Staff))
		->postJson(route('volunteer.time.checkin', $other), ['department_id' => $department->id])
		->assertForbidden();
});

it('requires a department from the same event to check in', function () {
	$otherDepartment = Department::factory()->create();

	$this->actingAs(userWithRole(Role::Staff))
		->postJson(route('volunteer.time.checkin', $this->event), ['department_id' => $otherDepartment->id])
		->assertUnprocessable()
		->assertJsonValidationErrors('department_id');
});

it('does not allow checking in twice', function () {
	$staff = userWithRole(Role::Staff);
	TimeEntry::factory()->ongoing()->create([
		'user_id' => $staff->id,
		'event_id' => $this->event->id,
		'department_id' => $this->department->id,
	]);

	$this->actingAs($staff)
		->postJson(route('volunteer.time.checkin', $this->event), ['department_id' => $this->department->id])
		->assertConflict()
		->assertJsonPath('error', 'Cannot check in with an already-ongoing time entry.');
});

it('checks out an ongoing time entry and returns updated stats', function () {
	$staff = userWithRole(Role::Staff);
	$entry = TimeEntry::factory()->create([
		'user_id' => $staff->id,
		'event_id' => $this->event->id,
		'department_id' => $this->department->id,
		'start' => now()->subHours(2),
		'stop' => null,
	]);

	$this->actingAs($staff)
		->postJson(route('volunteer.time.checkout', $entry))
		->assertOk()
		->assertJsonPath('stats.total', 7200);

	expect($entry->fresh()->isOngoing())->toBeFalse();

	$this->actingAs($staff)
		->postJson(route('volunteer.time.checkout', $entry))
		->assertConflict()
		->assertJsonPath('error', 'The time entry has already been checked out.');
});

it('does not let volunteers check out other volunteers', function () {
	$entry = TimeEntry::factory()->ongoing()->create([
		'event_id' => $this->event->id,
		'department_id' => $this->department->id,
	]);

	$this->actingAs(userWithRole(Role::Staff))
		->postJson(route('volunteer.time.checkout', $entry))
		->assertForbidden();

	$this->actingAs(userWithRole(Role::Manager))
		->postJson(route('volunteer.time.checkout', $entry))
		->assertOk();
});

it('lets managers create finished time entries for volunteers, keeping notes', function () {
	$manager = userWithRole(Role::Manager);
	$start = now()->subHours(3)->startOfMinute();

	$this->actingAs($manager)
		->putJson(route('volunteer.time.store', [$this->event, $this->volunteer]), [
			'department_id' => $this->department->id,
			'start' => $start->toISOString(),
			'stop' => $start->avoidMutation()->addHours(2)->toISOString(),
			'notes' => 'Forgot to clock in',
		])
		->assertOk();

	$entry = $this->volunteer->timeEntries()->sole();
	expect($entry->getDuration())->toEqual(7200)
		->and($entry->notes)->toBe('Forgot to clock in')
		->and($entry->creator_user_id)->toBe($manager->id);
});

it('rejects time entries that stop before they start', function () {
	$start = now()->subHours(3);

	$this->actingAs(userWithRole(Role::Manager))
		->putJson(route('volunteer.time.store', [$this->event, $this->volunteer]), [
			'department_id' => $this->department->id,
			'start' => $start->toISOString(),
			'stop' => $start->avoidMutation()->subHour()->toISOString(),
		])
		->assertUnprocessable()
		->assertJsonValidationErrors('stop');
});

it('does not let volunteers create time entries for other users', function () {
	$this->actingAs(userWithRole(Role::Staff))
		->putJson(route('volunteer.time.store', [$this->event, $this->volunteer]), [
			'department_id' => $this->department->id,
		])
		->assertForbidden();
});

it('only lets managers delete time entries for the active event, and admins any', function () {
	$entry = TimeEntry::factory()->finished()->create([
		'user_id' => $this->volunteer->id,
		'event_id' => $this->event->id,
		'department_id' => $this->department->id,
	]);
	$oldEntry = TimeEntry::factory()->finished()->create();

	$this->actingAs($this->volunteer)->deleteJson(route('volunteer.time.destroy', $entry))->assertForbidden();
	$this->actingAs(userWithRole(Role::Manager))->deleteJson(route('volunteer.time.destroy', $oldEntry))->assertForbidden();
	$this->actingAs(userWithRole(Role::Manager))->deleteJson(route('volunteer.time.destroy', $entry))->assertStatus(205);
	$this->actingAs(userWithRole(Role::Admin))->deleteJson(route('volunteer.time.destroy', $oldEntry))->assertStatus(205);

	expect(TimeEntry::count())->toBe(0);
});
