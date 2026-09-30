<?php

use App\Models\Department;
use App\Models\Event;
use App\Models\Role;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects creating a second ongoing time entry for the same user and event', function () {
	$manager = User::factory()->create(['role' => Role::Admin]);
	$volunteer = User::factory()->create();
	$event = Event::factory()->create();
	$department = Department::factory()->create(['event_id' => $event->id]);

	$this->actingAs($manager);

	// Mirrors the real clock-in UI, which always sends an explicit 'start' and omits 'stop'.
	$this->putJson(route('volunteer.time.store', [$event, $volunteer]), [
		'department_id' => $department->id,
		'start' => now()->subSecond()->toISOString(),
	])->assertOk();

	expect(TimeEntry::where('user_id', $volunteer->id)->whereNull('stop')->count())->toBe(1);

	$this->putJson(route('volunteer.time.store', [$event, $volunteer]), [
		'department_id' => $department->id,
		'start' => now()->subSecond()->toISOString(),
	])
		->assertStatus(409)
		->assertJson(['error' => 'User already has an ongoing time entry.']);

	expect(TimeEntry::where('user_id', $volunteer->id)->whereNull('stop')->count())->toBe(1);
});

it('allows creating a time entry with an explicit stop while another is ongoing', function () {
	$manager = User::factory()->create(['role' => Role::Admin]);
	$volunteer = User::factory()->create();
	$event = Event::factory()->create();
	$department = Department::factory()->create(['event_id' => $event->id]);

	$this->actingAs($manager);

	$this->putJson(route('volunteer.time.store', [$event, $volunteer]), [
		'department_id' => $department->id,
		'start' => now()->subSecond()->toISOString(),
	])->assertOk();

	$this->putJson(route('volunteer.time.store', [$event, $volunteer]), [
		'department_id' => $department->id,
		'start' => now()->subHour()->toISOString(),
		'stop' => now()->toISOString(),
	])->assertOk();

	expect(TimeEntry::where('user_id', $volunteer->id)->count())->toBe(2);
	expect(TimeEntry::where('user_id', $volunteer->id)->whereNull('stop')->count())->toBe(1);
});