<?php
// Current Laravel skeleton version: v11.1.4

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		web: __DIR__.'/../routes/web.php',
		api: __DIR__.'/../routes/api.php',
		commands: __DIR__.'/../routes/console.php',
		health: '/up',
	)
	->withMiddleware(function (Middleware $middleware) {
		$middleware->alias([
			'not-banned' => \App\Http\Middleware\RedirectIfBanned::class,
			'lockdown' => \App\Http\Middleware\RedirectDuringLockdown::class,
			'role' => \App\Http\Middleware\EnsureUserHasRole::class,
		]);

		$middleware->redirectTo(
			guests: fn (Request $request) => route('auth.login'),
			users: fn (Request $request) => route('tracker.index'),
		);

		$middleware->throttleApi();
	})
	->withExceptions(function (Exceptions $exceptions) {
		//
	})->create();
