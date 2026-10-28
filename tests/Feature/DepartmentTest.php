<?php

use App\Models\Department;
use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->withoutVite();
	$this->event = activateEvent();
	$this->admin = userWithRole(Role::Admin);
	$this->department = Department::factory()->visible()->create(['event_id' => $this->event->id, 'name' => 'Security']);
});

it('lists the departments of an event for managers', function () {
	$this->actingAs(userWithRole(Role::Manager))
		->getJson(route('events.departments.index', $this->event))
		->assertOk()
		->assertJsonPath('departments.0.name', 'Security');
});

it('lists the departments of the active event for volunteers', function () {
	$this->actingAs(userWithRole(Role::Volunteer))
		->getJson(route('events.departments.index', $this->event))
		->assertOk()
		->assertJsonCount(1, 'departments');
});

it('renders the department editor', function () {
	$this->actingAs($this->admin)
		->get(route('events.departments.index', $this->event))
		->assertOk()
		->assertInertia(fn (AssertableInertia $page) => $page
			->component('EventCrud')
			->where('event.id', $this->event->id)
			->has('events', 1)
			->where('departments.0.name', 'Security'));
});

it('shows departments of the active event to everyone and others to managers', function () {
	$old = Department::factory()->create();
	$volunteer = userWithRole(Role::Volunteer);

	$this->actingAs($volunteer)->getJson(route('departments.show', $this->department))->assertOk();
	$this->actingAs($volunteer)->getJson(route('departments.show', $old))->assertForbidden();
	$this->actingAs(userWithRole(Role::Manager))->getJson(route('departments.show', $old))->assertOk();
});

it('lets admins create departments with names unique to the event', function () {
	$this->actingAs($this->admin)
		->postJson(route('events.departments.store', $this->event), ['name' => 'Registration', 'hidden' => false])
		->assertOk()
		->assertJsonPath('department.event_id', $this->event->id);

	$this->actingAs($this->admin)
		->postJson(route('events.departments.store', $this->event), ['name' => 'Security', 'hidden' => false])
		->assertUnprocessable()
		->assertJsonValidationErrors('name');

	// The same name can be used in another event
	$this->actingAs($this->admin)
		->postJson(route('events.departments.store', Event::factory()->create()), ['name' => 'Security', 'hidden' => false])
		->assertOk();
});

it('requires the hidden flag for new departments', function () {
	$this->actingAs($this->admin)
		->postJson(route('events.departments.store', $this->event), ['name' => 'Registration'])
		->assertUnprocessable()
		->assertJsonValidationErrors('hidden');
});

it('lets admins update departments, requiring every field for PUT', function () {
	$this->actingAs($this->admin)
		->patchJson(route('departments.update', $this->department), ['hidden' => true])
		->assertOk();
	expect($this->department->fresh()->hidden)->toBeTrue();

	$this->actingAs($this->admin)
		->putJson(route('departments.update', $this->department), ['hidden' => false])
		->assertUnprocessable()
		->assertJsonValidationErrors('name');
});

it('lets admins delete departments', function () {
	$this->actingAs($this->admin)
		->deleteJson(route('departments.destroy', $this->department))
		->assertStatus(205);

	expect($this->department->fresh()->trashed())->toBeTrue()
		->and($this->department->fresh()->display_name)->toBe('Security (del)');
});

it('does not let managers change departments', function () {
	$manager = userWithRole(Role::Manager);

	$this->actingAs($manager)
		->postJson(route('events.departments.store', $this->event), ['name' => 'New', 'hidden' => false])
		->assertForbidden();
	$this->actingAs($manager)->patchJson(route('departments.update', $this->department), ['hidden' => true])->assertForbidden();
	$this->actingAs($manager)->deleteJson(route('departments.destroy', $this->department))->assertForbidden();
});
