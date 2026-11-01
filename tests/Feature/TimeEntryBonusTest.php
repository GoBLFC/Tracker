<?php

use App\Models\Department;
use App\Models\Event;
use App\Models\TimeBonus;
use App\Models\TimeEntry;
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

it('calculates bonus time without preloaded bonuses', function () {
	// Overlaps the entry by 2 hours at double time
	$bonus = TimeBonus::factory()->create([
		'event_id' => $this->event->id,
		'start' => $this->start->avoidMutation()->addHours(2),
		'stop' => $this->start->avoidMutation()->addHours(6),
		'modifier' => 2,
	]);
	$bonus->departments()->attach($this->department);

	// A bonus for another department doesn't apply
	$otherBonus = TimeBonus::factory()->create([
		'event_id' => $this->event->id,
		'start' => $this->start,
		'stop' => $this->start->avoidMutation()->addHours(4),
		'modifier' => 3,
	]);
	$otherBonus->departments()->attach(Department::factory()->create(['event_id' => $this->event->id]));

	expect($this->entry->calculateBonusTime($this->event))->toEqual(7200)
		->and($this->entry->calculateTotalTime($this->event))->toEqual(14400 + 7200);
});
