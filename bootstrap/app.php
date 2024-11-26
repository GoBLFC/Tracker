<?php

// Current Laravel skeleton version: v11.3.3

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		web: __DIR__ . '/../routes/web.php',
		api: __DIR__ . '/../routes/api.php',
		commands: __DIR__ . '/../routes/console.php',
		health: '/up',
	)
	->withMiddleware(function (Middleware $middleware) {
		$middleware->web(append: [
			HandleInertiaRequests::class,
		]);

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
		$exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
			$isLocal = app()->environment(['local', 'testing']);
			$isHandledStatus = in_array($response->getStatusCode(), [500, 503, 404, 403]);

			if (!$isLocal && $isHandledStatus) {
				return Inertia::render('Error', ['status' => $response->getStatusCode()])
					->toResponse($request)
					->setStatusCode($response->getStatusCode());
			} elseif ($response->getStatusCode() === 419) {
				return back()->with(['error' => 'The page expired, please try again.']);
			}

			return $response;
		});
	})->create();
