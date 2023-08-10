<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Tracker Day Boundary Hour
    |--------------------------------------------------------------------------
    |
    | The hour of the day that is treated as the boundary for volunteer
	| shifts/time entries. Midnight (00:00) would be 0, and 4am (04:00) is 4.
    |
    */

	'day_boundary_hour' => env('TRACKER_DAY_BOUNDARY_HOUR', 4),

];
