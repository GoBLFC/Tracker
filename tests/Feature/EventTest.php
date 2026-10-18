<?php

use App\Models\Department;
use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->withoutVite();
	$this->event = activateEvent(Event::factory()->create(['name' => 'BLFC 2026']));
	$this->admin = userWithRole(Role::Admin);
});

it('lists events for managers only', function () {
	$this->actingAs(userWithRole(Role::Lead))->getJson(route('events.index'))->assertForbidden();

	$this->actingAs(userWithRole(Role::Manager))
		->getJson(route('events.index'))
		->assertOk()
		->assertJsonPath('events.0.name', 'BLFC 2026');
});

it('sends the events page to the active event, or lists events when there is none', function () {
	$this->actingAs($this->admin)
		->get(route('events.index'))
		->assertRedirect(route('events.departments.index', $this->event));

	Setting::set('active-event', null);
	$this->actingAs($this->admin)
		->get(route('events.index'))
		->assertOk()
		->assertInertia(fn (AssertableInertia $page) => $page
			->component('EventCrud')
			->where('event', null)
			->has('events', 1));
});

it('lets admins create events', function () {
	$this->actingAs($this->admin)
		->postJson(route('events.store'), ['name' => 'BLFC 2027'])
		->assertOk()
		->assertJsonPath('event.name', 'BLFC 2027');

	expect(Event::whereName('BLFC 2027')->exists())->toBeTrue();
});

it('copies the departments of another event when creating one', function () {
	Department::factory()->visible()->create(['event_id' => $this->event->id, 'name' => 'Security']);
	Department::factory()->hidden()->create(['event_id' => $this->event->id, 'name' => 'Secret']);

	$this->actingAs($this->admin)
		->post(route('events.store'), ['name' => 'BLFC 2027', 'cloneEvent' => $this->event->id])
		->assertRedirect()
		->assertSessionHas('success', 'Created event BLFC 2027.');

	$departments = Event::whereName('BLFC 2027')->sole()->departments()->orderBy('name')->get();
	expect($departments->pluck('name')->all())->toBe(['Secret', 'Security'])
		->and($departments->pluck('hidden')->all())->toBe([true, false])
		->and(Department::count())->toBe(4);
});

it('validates new events', function (array $data, string $field) {
	$this->actingAs($this->admin)
		->postJson(route('events.store'), $data)
		->assertUnprocessable()
		->assertJsonValidationErrors($field);
})->with([
	[['name' => ''], 'name'],
	[['name' => 'BLFC 2026'], 'name'],
	[['name' => str_repeat('a', 65)], 'name'],
	[['name' => 'BLFC 2027', 'cloneEvent' => 'not-a-uuid'], 'cloneEvent'],
]);

it('allows reusing the name of a deleted event', function () {
	$this->event->delete();

	$this->actingAs($this->admin)->postJson(route('events.store'), ['name' => 'BLFC 2026'])->assertOk();
});

it('does not let managers create, rename, or delete events', function () {
	$manager = userWithRole(Role::Manager);

	$this->actingAs($manager)->postJson(route('events.store'), ['name' => 'New'])->assertForbidden();
	$this->actingAs($manager)->patchJson(route('events.update', $this->event), ['name' => 'New'])->assertForbidden();
	$this->actingAs($manager)->deleteJson(route('events.destroy', $this->event))->assertForbidden();
});

it('shows events to volunteers only while they are active', function () {
	$volunteer = userWithRole(Role::Volunteer);
	$old = Event::factory()->create();

	$this->actingAs($volunteer)->getJson(route('events.show', $this->event))->assertOk();
	$this->actingAs($volunteer)->getJson(route('events.show', $old))->assertForbidden();
	$this->actingAs($volunteer)
		->get(route('events.show', $this->event))
		->assertRedirect(route('events.departments.index', $this->event));
});

it('lets admins rename events', function () {
	$this->actingAs($this->admin)
		->patchJson(route('events.update', $this->event), ['name' => 'BLFC Renamed'])
		->assertOk();

	expect($this->event->fresh()->name)->toBe('BLFC Renamed');
});

it('lets admins delete events, clearing the active event', function () {
	$this->actingAs($this->admin)
		->delete(route('events.destroy', $this->event))
		->assertRedirect(route('events.index'))
		->assertSessionHas('success', 'Deleted event BLFC 2026.');

	TestCase::resetStaticCaches();
	expect($this->event->fresh()->trashed())->toBeTrue()
		->and(Setting::activeEvent())->toBeNull();
});
