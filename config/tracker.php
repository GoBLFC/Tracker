<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Tracker Timezone
	|--------------------------------------------------------------------------
	|
	| The timezone to display dates/times in and interpret user input as.
	| This must be a valid timezone recognized by PHP. See the manual:
	| https://www.php.net/manual/en/timezones.php
	|
	*/

	'timezone' => env('TRACKER_TIMEZONE', 'America/Los_Angeles'),

	/*
	|--------------------------------------------------------------------------
	| Tracker Day Boundary Hour
	|--------------------------------------------------------------------------
	|
	| The hour of the day that is treated as the boundary for volunteer
	| shifts/time entries. Ongoing entries will auto-close at this hour, and
	| "Time Today" will consider this the start of the day.
	|
	| Midnight (00:00) would be 0, and 4am (04:00) is 4.
	|
	*/

	'day_boundary_hour' => env('TRACKER_DAY_BOUNDARY_HOUR', 4),

	/*
	|--------------------------------------------------------------------------
	| Tracker Kiosk Authorization Lifetime
	|--------------------------------------------------------------------------
	|
	| The duration (in minutes) that kiosks remain authorized for.
	|
	*/

	'kiosk_lifetime' => env('TRACKER_KIOSK_LIFETIME', 60 * 24 * 7),

];
