<?php

use App\Facades\ConCat;
use App\Models\Department;
use App\Models\QuickCode;
use App\Models\Reward;
use App\Models\RewardClaim;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Models\User;
use App\Notifications\RewardAvailable;
use App\Notifications\TimeEntryAutoStopped;
use App\Telegram\Commands\HelpCommand;
use App\Telegram\Commands\PlzHelpCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->event = activateEvent();
	$this->department = Department::factory()->create(['event_id' => $this->event->id]);
});

it('stops ongoing time entries, crediting at most an hour, and notifies their users', function () {
	Notification::fake();
	$this->freezeSecond();
	$long = TimeEntry::factory()->create(['start' => now()->subHours(5), 'stop' => null]);
	$short = TimeEntry::factory()->create(['start' => now()->subMinutes(30), 'stop' => null]);
	$finished = TimeEntry::factory()->finished()->notAutoStopped()->create();

	$this->artisan('tracker:stop-ongoing')
		->expectsOutput('Stopping 2 ongoing time entries...')
		->expectsOutput('2 ongoing time entries stopped.')
		->assertSuccessful();

	$long->refresh();
	$short->refresh();
	expect($long->stop->equalTo($long->start->avoidMutation()->addHour()))->toBeTrue()
		->and($long->auto)->toBeTrue()
		->and($short->stop->equalTo(now()))->toBeTrue()
		->and($short->auto)->toBeTrue()
		->and($finished->fresh()->auto)->toBeFalse();
	Notification::assertSentTo($long->user, TimeEntryAutoStopped::class);
	Notification::assertSentTo($short->user, TimeEntryAutoStopped::class);
});

it('reports when there are no ongoing time entries to stop', function () {
	$this->artisan('tracker:stop-ongoing')
		->expectsOutput('No ongoing time entries to stop.')
		->assertSuccessful();
});

it('prefixes output with a timestamp when loggable', function () {
	// The timestamp comes from PHP's date(), so it can't be frozen
	$this->artisan('tracker:stop-ongoing', ['--loggable' => true])
		->expectsOutputToContain('] info: No ongoing time entries to stop.')
		->assertSuccessful();
});

it('notifies volunteers of rewards they newly qualify for, once', function () {
	$user = User::factory()->telegramUnlinked()->create();
	timeEntryFor($user, $this->department, now()->subDay(), 9 * 60);
	$claimed = Reward::factory()->create(['event_id' => $this->event->id, 'hours' => 2]);
	$eligible = Reward::factory()->create(['event_id' => $this->event->id, 'hours' => 8]);
	Reward::factory()->create(['event_id' => $this->event->id, 'hours' => 12]);
	$claim = new RewardClaim;
	$claim->user_id = $user->id;
	$claim->reward_id = $claimed->id;
	$claim->save();

	$this->artisan('tracker:notify-rewards')
		->expectsOutput('Notified 1 user of eligible rewards.')
		->assertSuccessful();

	$notification = $user->notifications()->sole();
	expect($notification->type)->toBe(RewardAvailable::class)
		->and($notification->data['reward_id'])->toBe($eligible->id);

	$this->artisan('tracker:notify-rewards')
		->expectsOutput('Notified 0 users of eligible rewards.')
		->assertSuccessful();
	expect($user->notifications()->count())->toBe(1);
});

it('does not notify for rewards without an active event', function () {
	Setting::set('active-event', null);

	$this->artisan('tracker:notify-rewards')
		->expectsOutput('No active event to process time entries for.')
		->assertSuccessful();
});

it('fills in unknown users from ConCat', function () {
	$withRegistration = User::factory()->create(['username' => 'unknown', 'badge_id' => 1111, 'badge_name' => null]);
	$withUserOnly = User::factory()->create(['username' => 'unknown', 'badge_id' => 2222]);
	$missing = User::factory()->create(['username' => 'unknown', 'badge_id' => 3333]);

	ConCat::shouldReceive('authorize')->once();
	ConCat::shouldReceive('searchRegistrationsByUserIds')->once()->with(['1111', '2222', '3333'])->andReturn([
		(object) [
			'badgeName' => 'Foxy',
			'user' => (object) ['id' => 1111, 'username' => 'foxy', 'firstName' => 'Fox', 'lastName' => 'Face'],
		],
	]);
	ConCat::shouldReceive('getUser')->with(2222)->andReturn(
		(object) ['id' => 2222, 'username' => 'wolfy', 'firstName' => 'Wolf', 'lastName' => 'Face'],
	);
	ConCat::shouldReceive('getUser')->with(3333)->andThrow(new RuntimeException('Not found'));

	$this->artisan('auth:fetch-unknown')
		->expectsOutput('There are 3 unknown users to look up.')
		->expectsOutput('Populated 2 unknown users.')
		->assertSuccessful();

	expect($withRegistration->fresh()->username)->toBe('foxy')
		->and($withRegistration->fresh()->badge_name)->toBe('Foxy')
		->and($withUserOnly->fresh()->username)->toBe('wolfy')
		->and($missing->fresh()->username)->toBe('unknown');
});

it('does not contact ConCat when there are no unknown users', function () {
	ConCat::shouldReceive('authorize')->never();

	$this->artisan('auth:fetch-unknown')
		->expectsOutput('No unknown users to retrieve details for.')
		->assertSuccessful();
});

it('sets the role of a user by badge ID or ID', function () {
	$user = User::factory()->create(['badge_id' => 1234, 'badge_name' => 'Foxy']);

	$this->artisan('auth:set-role', ['user' => '1234', 'role' => 'manager'])
		->expectsOutput('Changed the role of Foxy (#1234) from Volunteer to Manager.')
		->assertSuccessful();
	expect($user->fresh()->role)->toBe(Role::Manager);

	$this->artisan('auth:set-role', ['user' => $user->id, 'role' => 'Admin'])->assertSuccessful();
	expect($user->fresh()->role)->toBe(Role::Admin);
});

it('sends the visible bot commands to Telegram', function () {
	$telegram = fakeTelegram();
	$telegram->shouldReceive('getCommands')->andReturn(['help' => new HelpCommand, 'plzhelp' => new PlzHelpCommand]);
	$telegram->shouldReceive('setMyCommands')->once()->with(Mockery::on(
		fn (array $params) => $params['commands']->all() === [
			['command' => 'help', 'description' => 'View status and available commands'],
		],
	));

	$this->artisan('telegram:set-commands')->expectsOutput('Command list sent to Telegram.')->assertSuccessful();
});

it('sets and removes the Telegram webhook', function () {
	$telegram = fakeTelegram();
	$telegram->shouldReceive('setWebhook')->once()->with(Mockery::on(
		fn (array $params) => $params['url'] === route('telegram.tracker.webhook'),
	));
	$telegram->shouldReceive('removeWebhook')->once();

	$this->artisan('telegram:set-webhook')->expectsOutput('Webhook sent to Telegram.')->assertSuccessful();
	$this->artisan('telegram:set-webhook', ['--remove' => true])
		->expectsOutput('Webhook removal sent to Telegram.')
		->assertSuccessful();
});

it('generates 4 character quick codes that expire after 30 seconds', function () {
	$quickCode = new QuickCode;
	$quickCode->user_id = User::factory()->create()->id;
	$quickCode->save();

	expect($quickCode->code)->toMatch('/^[0-9A-Z]{4}$/')
		->and($quickCode->isExpired())->toBeFalse()
		->and(QuickCode::unexpired()->count())->toBe(1);

	$this->travel(30)->seconds();
	expect($quickCode->isExpired())->toBeTrue()
		->and(QuickCode::expired()->count())->toBe(1);

	$this->artisan('model:prune', ['--model' => QuickCode::class])->assertSuccessful();
	expect(QuickCode::count())->toBe(0);
});
