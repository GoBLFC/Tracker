<?php

use App\Models\Department;
use App\Models\Event;
use App\Models\Role;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->event = Event::factory()->create();
	$this->department = Department::factory()->create(['event_id' => $this->event->id]);
	$this->start = Carbon::parse('2026-07-01 17:00:00');
	$this->entry = TimeEntry::factory()->create([
		'event_id' => $this->event->id,
		'department_id' => $this->department->id,
		'start' => $this->start,
		'stop' => $this->start->avoidMutation()->addHours(4),
	]);
});

it('redirects back after creating a time entry from a form', function () {
	$admin = User::factory()->create(['role' => Role::Admin]);
	$volunteer = User::factory()->create();

	$this->actingAs($admin)
		->from('/manage')
		->put(route('volunteer.time.store', [$this->event, $volunteer]), [
			'department_id' => $this->department->id,
			'start' => now()->subHours(2)->toISOString(),
			'stop' => now()->subHour()->toISOString(),
		])
		->assertRedirect('/manage')
		->assertSessionHas('success', 'Created time entry.');

	expect($volunteer->timeEntries()->count())->toBe(1);
});

it('redirects back after deleting a time entry from a form', function () {
	$admin = User::factory()->create(['role' => Role::Admin]);

	$this->actingAs($admin)
		->from('/manage')
		->delete(route('volunteer.time.destroy', $this->entry))
		->assertRedirect('/manage')
		->assertSessionHas('success', 'Deleted time entry.');

	expect(TimeEntry::count())->toBe(0);
});
