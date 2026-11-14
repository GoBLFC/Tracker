<?php

use App\Models\Department;
use App\Models\Event;
use App\Models\Role;
use App\Models\TimeBonus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->event = activateEvent();
	$this->admin = userWithRole(Role::Admin);
	$this->departments = Department::factory()->count(2)->create(['event_id' => $this->event->id]);
});

/**
 * Builds valid input for a time bonus
 */
function timeBonusInput(array $departmentIds, array $overrides = []): array {
	return [
		'start' => '2026-07-01T10:00:00-07:00',
		'stop' => '2026-07-01T14:00:00-07:00',
		'modifier' => 1.5,
		'departments' => $departmentIds,
		...$overrides,
	];
}

it('lets admins create time bonuses for departments, storing times in UTC', function () {
	$this->actingAs($this->admin)
		->postJson(route('events.bonuses.store', $this->event), timeBonusInput($this->departments->pluck('id')->all()))
		->assertOk();

	$bonus = TimeBonus::sole();
	expect($bonus->start->equalTo(Carbon::parse('2026-07-01T17:00:00Z')))->toBeTrue()
		->and($bonus->getRawOriginal('start'))->toStartWith('2026-07-01 17:00:00')
		->and($bonus->modifier)->toBe(1.5)
		->and($bonus->departments()->count())->toBe(2);
});

it('validates new time bonuses', function (array $overrides, string $field) {
	$this->actingAs($this->admin)
		->postJson(route('events.bonuses.store', $this->event), timeBonusInput([$this->departments[0]->id], $overrides))
		->assertUnprocessable()
		->assertJsonValidationErrors($field);
})->with([
	'stop before start' => [['stop' => '2026-07-01T09:00:00-07:00'], 'stop'],
	'modifier below 1' => [['modifier' => 0.5], 'modifier'],
	'modifier above 10' => [['modifier' => 11], 'modifier'],
	'modifier with 3 decimals' => [['modifier' => 1.125], 'modifier'],
	'no departments' => [['departments' => []], 'departments'],
]);

it('only accepts departments from the same event', function () {
	$foreign = Department::factory()->create(['event_id' => Event::factory()->create()->id]);

	$this->actingAs($this->admin)
		->postJson(route('events.bonuses.store', $this->event), timeBonusInput([$foreign->id]))
		->assertUnprocessable()
		->assertJsonValidationErrors('departments');
});

it('lists the time bonuses of an event with their departments', function () {
	$bonus = TimeBonus::factory()->create(['event_id' => $this->event->id]);
	$bonus->departments()->attach($this->departments[0]);

	$this->actingAs(userWithRole(Role::Manager))
		->getJson(route('events.bonuses.index', $this->event))
		->assertOk()
		->assertJsonPath('bonuses.0.departments.0.id', $this->departments[0]->id);
});

it('updates time bonuses and replaces their departments', function () {
	$bonus = TimeBonus::factory()->create(['event_id' => $this->event->id, 'modifier' => 2]);
	$bonus->departments()->attach($this->departments[0]);

	$this->actingAs($this->admin)
		->patchJson(route('bonuses.update', $bonus), ['modifier' => 3, 'departments' => [$this->departments[1]->id]])
		->assertOk();

	$bonus->refresh();
	expect($bonus->modifier)->toBe(3.0)
		->and($bonus->departments->pluck('id')->all())->toBe([$this->departments[1]->id]);
});

it('keeps the departments of time bonuses when not given', function () {
	$bonus = TimeBonus::factory()->create(['event_id' => $this->event->id]);
	$bonus->departments()->attach($this->departments[0]);

	$this->actingAs($this->admin)
		->patchJson(route('bonuses.update', $bonus), ['stop' => '2030-01-01T00:00:00Z'])
		->assertOk();

	expect($bonus->departments()->count())->toBe(1);
});

it('lets admins delete time bonuses', function () {
	$bonus = TimeBonus::factory()->create(['event_id' => $this->event->id]);

	$this->actingAs($this->admin)->deleteJson(route('bonuses.destroy', $bonus))->assertStatus(205);

	expect(TimeBonus::count())->toBe(0);
});

it('does not let managers change time bonuses', function () {
	$manager = userWithRole(Role::Manager);
	$bonus = TimeBonus::factory()->create(['event_id' => $this->event->id]);

	$this->actingAs($manager)
		->postJson(route('events.bonuses.store', $this->event), timeBonusInput([$this->departments[0]->id]))
		->assertForbidden();
	$this->actingAs($manager)->patchJson(route('bonuses.update', $bonus), ['modifier' => 3])->assertForbidden();
	$this->actingAs($manager)->deleteJson(route('bonuses.destroy', $bonus))->assertForbidden();
});

it('lists time bonuses of the active event only for volunteers', function () {
	$volunteer = userWithRole(Role::Volunteer);

	$this->actingAs($volunteer)->getJson(route('events.bonuses.index', $this->event))->assertOk();
	$this->actingAs($volunteer)->getJson(route('events.bonuses.index', Event::factory()->create()))->assertForbidden();
});

it('shows time bonuses of the active event to volunteers', function () {
	$volunteer = userWithRole(Role::Volunteer);
	$bonus = TimeBonus::factory()->create(['event_id' => $this->event->id]);
	$old = TimeBonus::factory()->create();

	$this->actingAs($volunteer)->getJson(route('bonuses.show', $bonus))->assertOk();
	$this->actingAs($volunteer)->getJson(route('bonuses.show', $old))->assertForbidden();
});
