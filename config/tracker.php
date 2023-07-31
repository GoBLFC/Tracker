<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Tracker Day Boundary Hour
    |--------------------------------------------------------------------------
    |
    | The hour of the day that is treated as the boundary for volunteer
	| shifts/time entries. Midnight would be 0, and 4am is 4.
    |
    */

	'day_boundary_hour' => env('TRACKER_DAY_BOUNDARY_HOUR', 4),

];
