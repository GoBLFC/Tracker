<?php

use App\Models\TimeEntry;
use Illuminate\Support\Carbon;

it('calculates the overlap of two time periods in seconds', function (string $start1, string $end1, string $start2, string $end2, int $expected) {
	expect(TimeEntry::calculateOverlap(
		Carbon::parse($start1),
		Carbon::parse($end1),
		Carbon::parse($start2),
		Carbon::parse($end2),
	))->toBe($expected);
})->with([
	'partial overlap' => ['10:00', '14:00', '12:00', '16:00', 7200],
	'contained' => ['10:00', '14:00', '11:00', '12:00', 3600],
	'containing' => ['11:00', '12:00', '10:00', '14:00', 3600],
	'touching' => ['10:00', '12:00', '12:00', '14:00', 0],
	'apart' => ['10:00', '11:00', '13:00', '14:00', 0],
]);

it('formats durations for humans', function (int $seconds, string $expected) {
	expect(TimeEntry::humanDuration($seconds))->toBe($expected);
})->with([
	[24000, '6h 40m'],
	[90000, '25h'],
	[59, '59s'],
]);

it('formats durations like a clock', function (int $seconds, string $expected) {
	expect(TimeEntry::clockDuration($seconds))->toBe($expected);
})->with([
	[0, '0:00'],
	[65, '1:05'],
	[18392, '5:06:32'],
	[3600, '1:00:00'],
]);
