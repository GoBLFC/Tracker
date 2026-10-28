<?php

use App\Models\Kiosk;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
	activateEvent();
	$this->lead = userWithRole(Role::Lead);
});

it('lets leads authorize their session as a kiosk', function () {
	$response = $this->actingAs($this->lead)
		->postJson(route('kiosks.authorize.post'))
		->assertOk();

	$kiosk = Kiosk::sole();
	$response->assertJsonPath('kiosk.id', $kiosk->id)->assertCookie('kiosk', $kiosk->session_key);
	expect(strlen($kiosk->session_key))->toBe(32);
});

it('does not let volunteers authorize kiosks', function () {
	$this->actingAs(User::factory()->create())->postJson(route('kiosks.authorize.post'))->assertForbidden();
	expect(Kiosk::count())->toBe(0);
});

it('does not authorize a session that is already a kiosk', function () {
	$kiosk = Kiosk::create();

	$this->actingAs($this->lead)
		->withCredentials()
		->withCookie('kiosk', $kiosk->session_key)
		->postJson(route('kiosks.authorize.post'))
		->assertJsonPath('error', 'Session is already authorized as a kiosk.');

	expect(Kiosk::count())->toBe(1);
});

it('lets leads deauthorize the kiosk for their session', function () {
	$kiosk = Kiosk::create();

	$this->actingAs($this->lead)
		->withCredentials()
		->withCookie('kiosk', $kiosk->session_key)
		->postJson(route('kiosks.deauthorize.post'))
		->assertStatus(205)
		->assertCookieExpired('kiosk');

	expect(Kiosk::count())->toBe(0);
});

it('does not deauthorize sessions that are not kiosks', function () {
	$this->actingAs($this->lead)->postJson(route('kiosks.deauthorize.post'))->assertForbidden();
});

it('recognizes kiosk sessions until they expire', function () {
	$kiosk = Kiosk::create();
	request()->cookies->set('kiosk', $kiosk->session_key);

	expect(Kiosk::isSessionAuthorized(true))->toBeTrue();

	$this->travel(config('tracker.kiosk_lifetime') + 1)->minutes();
	TestCase::resetStaticCaches();
	expect(Kiosk::isSessionAuthorized(true))->toBeFalse()
		->and(Kiosk::expired()->count())->toBe(1);
});

it('treats every session as a kiosk in dev mode unless strict', function () {
	App\Models\Setting::set('dev-mode', true);

	expect(Kiosk::isSessionAuthorized())->toBeTrue()
		->and(Kiosk::isSessionAuthorized(true))->toBeFalse();
});

it('prunes expired kiosks', function () {
	Kiosk::create();
	$this->travel(config('tracker.kiosk_lifetime') + 1)->minutes();
	Kiosk::create();

	$this->artisan('model:prune', ['--model' => Kiosk::class])->assertSuccessful();

	expect(Kiosk::count())->toBe(1);
});
