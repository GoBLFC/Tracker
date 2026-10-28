<?php

use App\Events\QuickCodeLogin;
use App\Models\AttendeeLog;
use App\Models\Kiosk;
use App\Models\QuickCode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event as EventFacade;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->withoutVite();
	$this->event = activateEvent(App\Models\Event::factory()->create(['name' => 'BLFC 2026']));
	fakeTelegram()->shouldReceive('getMe')->andReturn((object) ['username' => 'TrackerBot']);
});

it('shares the user, active event, and site state with every page', function () {
	$user = User::factory()->create(['badge_name' => 'Foxy']);

	$this->actingAs($user)
		->get(route('volunteer.index'))
		->assertInertia(fn (AssertableInertia $page) => $page
			->where('auth.user.id', $user->id)
			->where('auth.user.badge_name', 'Foxy')
			->missing('auth.user.first_name')
			->where('activeEvent', ['id' => $this->event->id, 'name' => 'BLFC 2026'])
			->where('timezone', config('tracker.timezone'))
			->where('isGatekeeper', false)
			->where('isDevMode', false)
			->where('isKiosk', false));
});

it('shares whether the user is a gatekeeper and the session is a kiosk', function () {
	$user = User::factory()->create();
	$log = new AttendeeLog(['name' => 'Lounge']);
	$log->event_id = $this->event->id;
	$log->save();
	$log->users()->attach($user, ['type' => 'gatekeeper']);
	$kiosk = Kiosk::create();

	$this->actingAs($user)
		->withCookie('kiosk', $kiosk->session_key)
		->get(route('volunteer.index'))
		->assertInertia(fn (AssertableInertia $page) => $page
			->where('isGatekeeper', true)
			->where('isKiosk', true));
});

it('shares flash messages', function () {
	$this->actingAs(userWithRole(Role::Admin))
		->withSession(['success' => 'It worked.'])
		->get(route('volunteer.index'))
		->assertInertia(fn (AssertableInertia $page) => $page
			->where('flash.success', 'It worked.')
			->where('flash.error', null));
});

it('logs logins, logouts, and quick code use', function () {
	$messages = collect();
	EventFacade::listen(MessageLogged::class, fn (MessageLogged $log) => $messages->push($log->message));
	$user = User::factory()->create();
	$quickCode = new QuickCode;
	$quickCode->user_id = $user->id;
	$quickCode->save();

	Auth::login($user);
	Auth::logout();
	QuickCodeLogin::dispatch($quickCode);

	expect($messages->all())->toContain('User logged in', 'User logged out', 'Quick code used');
});
