<?php

use App\Models\AttendeeLog;
use App\Models\Department;
use App\Models\Event;
use App\Models\Reward;
use App\Models\RewardClaim;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TimeBonus;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
	config(['tracker.timezone' => 'America/Los_Angeles', 'tracker.day_boundary_hour' => 4]);
	$this->event = activateEvent();
	$this->department = Department::factory()->create(['event_id' => $this->event->id]);
	$this->user = User::factory()->create();
});

/**
 * Parses a time in the tracker's timezone and converts it to UTC, which models store times in
 */
function trackerTime(string $time): Carbon {
	return Carbon::parse($time, 'America/Los_Angeles')->utc();
}

/**
 * Creates a time bonus for departments
 */
function bonusFor(array $departments, string $start, string $stop, float $modifier): TimeBonus {
	$bonus = TimeBonus::factory()->create([
		'event_id' => $departments[0]->event_id,
		'start' => trackerTime($start),
		'stop' => trackerTime($stop),
		'modifier' => $modifier,
	]);
	$bonus->departments()->attach(collect($departments)->pluck('id'));
	return $bonus;
}

it('measures ongoing time entries up to now', function () {
	$this->freezeSecond();
	$entry = TimeEntry::factory()->create(['start' => now()->subMinutes(90), 'stop' => null]);

	expect($entry->getDuration())->toEqual(5400)
		->and($entry->getClockDuration())->toBe('1:30:00')
		->and($entry->getHumanDuration())->toBe('1h 30m');
});

it('adds bonus time for the overlap with bonuses on the entry\'s department', function () {
	timeEntryFor($this->user, $this->department, trackerTime('2026-07-01 10:00'), 240);
	bonusFor([$this->department], '2026-07-01 12:00', '2026-07-01 16:00', 2);
	bonusFor([Department::factory()->create(['event_id' => $this->event->id])], '2026-07-01 10:00', '2026-07-01 14:00', 3);

	expect($this->user->getTimeStats()['bonus'])->toEqual(7200)
		->and($this->user->getEarnedTime())->toBe(14400 + 7200);
});

it('stacks overlapping bonuses', function () {
	timeEntryFor($this->user, $this->department, trackerTime('2026-07-01 10:00'), 60);
	bonusFor([$this->department], '2026-07-01 10:00', '2026-07-01 11:00', 1.5);
	bonusFor([$this->department], '2026-07-01 10:30', '2026-07-01 12:00', 2);

	// 1h at +50% and 30m at +100%
	expect($this->user->getTimeStats()['bonus'])->toEqual(1800 + 1800);
});

it('totals earned time with bonuses for one event only', function () {
	timeEntryFor($this->user, $this->department, trackerTime('2026-07-01 10:00'), 120);
	bonusFor([$this->department], '2026-07-01 10:00', '2026-07-01 11:00', 2);
	$other = Department::factory()->create();
	timeEntryFor($this->user, $other, trackerTime('2026-07-01 10:00'), 600);

	expect($this->user->getEarnedTime($this->event))->toBe(7200 + 3600)
		->and($this->user->getEarnedTime())->toBe(7200 + 3600)
		->and($this->user->getEarnedTime($other->event))->toBe(36000);
});

it('counts time for today from the day boundary hour', function () {
	$this->travelTo(trackerTime('2026-07-02 12:00'));
	timeEntryFor($this->user, $this->department, trackerTime('2026-07-01 10:00'), 120);
	timeEntryFor($this->user, $this->department, trackerTime('2026-07-02 09:00'), 60);
	// Crosses the 4am boundary, so only the hour after it counts for today
	$crossing = timeEntryFor($this->user, $this->department, trackerTime('2026-07-02 02:00'), 180);

	$stats = $this->user->getTimeStats();

	expect($crossing->isCrossingDayBoundary())->toBeTrue()
		->and($crossing->getSecondsPastDayBoundary())->toEqual(3600)
		->and($stats['total'])->toEqual(21600)
		->and($stats['bonus'])->toEqual(0)
		->and($stats['day'])->toEqual(7200)
		->and($stats['entries'])->toHaveCount(3);
});

it('includes bonus time in the time stats', function () {
	$this->travelTo(trackerTime('2026-07-01 20:00'));
	timeEntryFor($this->user, $this->department, trackerTime('2026-07-01 10:00'), 60);
	$bonus = bonusFor([$this->department], '2026-07-01 10:00', '2026-07-01 11:00', 2);

	$stats = $this->user->getTimeStats();

	expect($stats['total'])->toEqual(7200)
		->and($stats['bonus'])->toEqual(3600)
		->and($stats['entries'][0]->bonus_time)->toEqual(3600)
		->and($stats['bonuses']->pluck('id')->all())->toBe([$bonus->id]);
});

it('returns empty stats without an active event', function () {
	Setting::set('active-event', null);

	expect($this->user->getTimeStats())->toMatchArray(['total' => 0, 'bonus' => 0, 'day' => 0])
		->and($this->user->getEarnedTime())->toBe(0)
		->and($this->user->getRewardInfo()['all'])->toBeEmpty();
});

it('works out which rewards are eligible and claimed', function () {
	timeEntryFor($this->user, $this->department, trackerTime('2026-07-01 10:00'), 8 * 60);
	$four = Reward::factory()->create(['event_id' => $this->event->id, 'hours' => 4]);
	$eight = Reward::factory()->create(['event_id' => $this->event->id, 'hours' => 8]);
	$twelve = Reward::factory()->create(['event_id' => $this->event->id, 'hours' => 12]);
	$claim = new RewardClaim;
	$claim->user_id = $this->user->id;
	$claim->reward_id = $four->id;
	$claim->save();

	$info = $this->user->getRewardInfo();

	// Rewards need more than their hours to be eligible
	expect($info['all'])->toHaveCount(3)
		->and($info['eligible']->pluck('id')->all())->toBe([$four->id])
		->and($info['claimed']->pluck('id')->all())->toBe([$four->id]);

	expect($this->user->getRewardInfo(earnedHours: 12.5)['eligible']->pluck('id')->sort()->values()->all())
		->toBe(collect([$four->id, $eight->id, $twelve->id])->sort()->values()->all());
});

it('checks roles inclusively unless strict', function () {
	$manager = User::factory()->create(['role' => Role::Manager]);

	expect($manager->isStaff())->toBeTrue()
		->and($manager->isLead())->toBeTrue()
		->and($manager->isManager())->toBeTrue()
		->and($manager->isAdmin())->toBeFalse()
		->and($manager->isLead(true))->toBeFalse()
		->and($manager->isManager(true))->toBeTrue()
		->and($manager->isBanned())->toBeFalse()
		->and(User::factory()->create(['role' => Role::Banned])->isBanned())->toBeTrue();
});

it('treats managers and gatekeepers of active event logs as gatekeepers', function () {
	$log = new AttendeeLog(['name' => 'Lounge']);
	$log->event_id = $this->event->id;
	$log->save();
	$gatekeeper = User::factory()->create();
	$log->users()->attach($gatekeeper, ['type' => 'gatekeeper']);

	expect($gatekeeper->isGatekeeper())->toBeTrue()
		->and($gatekeeper->hasAnyBackendAccess())->toBeTrue()
		->and(User::factory()->create(['role' => Role::Manager])->isGatekeeper())->toBeTrue()
		->and($this->user->isGatekeeper())->toBeFalse()
		->and($this->user->hasAnyBackendAccess())->toBeFalse()
		->and(User::factory()->create(['role' => Role::Lead])->hasAnyBackendAccess())->toBeTrue();
});

it('names users by badge name, then username, and marks deleted users', function () {
	$user = User::factory()->create(['badge_name' => 'Foxy', 'username' => 'foxy', 'badge_id' => 42]);
	$noBadgeName = User::factory()->create(['badge_name' => null, 'username' => 'wolfy']);

	expect($user->display_name)->toBe('Foxy')
		->and($user->audit_name)->toBe('Foxy (#42)')
		->and($noBadgeName->display_name)->toBe('wolfy');

	$user->delete();
	expect($user->display_name)->toBe('Foxy (del)');
});

it('gives new users a Telegram setup key', function () {
	$user = User::factory()->create();
	$key = $user->tg_setup_key;

	expect(strlen($key))->toBe(32)
		->and($user->generateTelegramSetupKey()->tg_setup_key)->not->toBe($key)
		->and($user->toArray())->not->toHaveKeys(['tg_setup_key', 'tg_chat_id']);
});

it('only stores real names for volunteers and above when created from a registration', function () {
	$registration = (object) [
		'badgeName' => 'Foxy',
		'user' => (object) ['id' => 1234, 'username' => 'foxy', 'firstName' => 'Fox', 'lastName' => 'Face'],
	];

	$attendee = User::createFromConCatRegistration($registration, Role::Attendee);
	expect($attendee->full_name)->toBe('Unidentified Attendee');

	$attendee->forceDelete();
	$volunteer = User::createFromConCatRegistration($registration, Role::Volunteer);
	expect($volunteer->full_name)->toBe('Fox Face')
		->and($volunteer->badge_name)->toBe('Foxy');
});

it('scopes entities to the active event by default', function () {
	$oldEvent = Event::factory()->create();
	Department::factory()->create(['event_id' => $oldEvent->id]);
	Reward::factory()->create(['event_id' => $this->event->id]);
	Reward::factory()->create(['event_id' => $oldEvent->id]);

	expect(Department::forEvent()->pluck('id')->all())->toBe([$this->department->id])
		->and(Department::forEvent($oldEvent)->count())->toBe(1)
		->and(Reward::forEvent()->count())->toBe(1)
		->and(Reward::forEvent($oldEvent->id)->count())->toBe(1)
		->and($this->department->isForActiveEvent())->toBeTrue();
});
