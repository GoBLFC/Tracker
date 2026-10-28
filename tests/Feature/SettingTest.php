<?php

use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->event = activateEvent();
	$this->admin = userWithRole(Role::Admin);
});

it('only lets admins view the settings', function () {
	$this->actingAs(userWithRole(Role::Manager))->getJson(route('settings.index'))->assertForbidden();

	$this->actingAs($this->admin)
		->getJson(route('settings.index'))
		->assertOk()
		->assertJsonCount(3, 'settings')
		->assertJsonPath('events.0.id', $this->event->id);
});

it('changes the active event', function () {
	$other = Event::factory()->create();

	$this->actingAs($this->admin)
		->from(route('settings.index'))
		->put(route('settings.update', 'active-event'), ['value' => $other->id])
		->assertRedirect(route('settings.index'))
		->assertSessionHas('success', 'Updated the active event.');

	TestCase::resetStaticCaches();
	expect(Setting::activeEvent()->id)->toBe($other->id);
});

it('clears the active event', function () {
	$this->actingAs($this->admin)
		->putJson(route('settings.update', 'active-event'), ['value' => null])
		->assertStatus(205);

	TestCase::resetStaticCaches();
	expect(Setting::activeEvent())->toBeNull();
});

it('rejects active events that do not exist', function () {
	$this->actingAs($this->admin)
		->putJson(route('settings.update', 'active-event'), ['value' => fake()->uuid()])
		->assertUnprocessable()
		->assertJsonValidationErrors('value');
});

it('toggles dev mode and the lockdown', function (string $setting, string $getter, string $on, string $off) {
	$this->actingAs($this->admin)
		->from(route('settings.index'))
		->put(route('settings.update', $setting), ['value' => '1'])
		->assertSessionHas('success', $on);
	TestCase::resetStaticCaches();
	expect(Setting::$getter())->toBeTrue();

	$this->actingAs($this->admin)
		->from(route('settings.index'))
		->put(route('settings.update', $setting), ['value' => '0'])
		->assertSessionHas('success', $off);
	TestCase::resetStaticCaches();
	expect(Setting::$getter())->toBeFalse();
})->with([
	['dev-mode', 'isDevMode', 'Enabled development mode.', 'Disabled development mode.'],
	['lockdown', 'isLockedDown', 'Locked the site down.', 'Lifted the lockdown.'],
]);

it('requires a value when updating a setting', function () {
	$this->actingAs($this->admin)
		->putJson(route('settings.update', 'lockdown'), [])
		->assertUnprocessable()
		->assertJsonValidationErrors('value');
});

it('clears the value of a setting', function () {
	$this->actingAs($this->admin)
		->deleteJson(route('settings.destroy', 'lockdown'))
		->assertStatus(205);

	expect(Setting::whereName('lockdown')->sole()->value)->toBeNull();
	TestCase::resetStaticCaches();
	expect(Setting::isLockedDown())->toBeFalse();
});

it('does not let managers change settings', function () {
	$manager = userWithRole(Role::Manager);

	$this->actingAs($manager)->putJson(route('settings.update', 'lockdown'), ['value' => true])->assertForbidden();
	$this->actingAs($manager)->deleteJson(route('settings.destroy', 'lockdown'))->assertForbidden();
});

it('refuses to set unknown settings', function () {
	Setting::set('made-up', true);
})->throws(ValueError::class, 'Unknown setting name: made-up');

it('caches setting values until they are changed', function () {
	expect(Setting::isLockedDown())->toBeFalse();

	// Changes made directly in the database aren't seen while the value is cached
	Setting::whereName('lockdown')->update(['value' => 'true']);
	expect(Setting::isLockedDown())->toBeFalse();

	// The value is cached in memory and in the cache store
	TestCase::resetStaticCaches();
	expect(Setting::isLockedDown())->toBeFalse();
	TestCase::resetStaticCaches();
	Cache::forget('setting:lockdown');
	expect(Setting::isLockedDown())->toBeTrue();

	Setting::set('lockdown', false);
	expect(Setting::isLockedDown())->toBeFalse();
});

it('clears the active event when its event is deleted', function () {
	$this->event->delete();

	TestCase::resetStaticCaches();
	expect(Setting::activeEvent())->toBeNull()
		->and($this->event->isActive())->toBeFalse();
});
