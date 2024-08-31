<?php

use App\Console\Commands\NotifyEligibleRewardsCommand;
use App\Console\Commands\StopOngoingTimeEntriesCommand;
// use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Prune models every day
Schedule::command('model:prune --no-ansi')
	->daily()
	->appendOutputTo(storage_path('logs/prune.log'));

// Stop ongoing time entries every day at the configured day boundary hour
Schedule::command(StopOngoingTimeEntriesCommand::class, ['--loggable', '--no-ansi'])
	->dailyAt(config('tracker.day_boundary_hour') . ':00')
	->timezone(config('tracker.timezone'))
	->appendOutputTo(storage_path('logs/stop-ongoing.log'));

// Notify volunteers of rewards they are newly eligible for every 5min
Schedule::command(NotifyEligibleRewardsCommand::class, ['--loggable', '--no-ansi'])
	->everyFiveMinutes()
	->appendOutputTo(storage_path('logs/notify-rewards.log'));
