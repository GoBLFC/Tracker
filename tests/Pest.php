<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(
	Tests\TestCase::class,
	// Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
	return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something() {
	// ..
}

/**
 * Makes an event the active one and opens the site up by lifting the lockdown and turning off dev mode.
 * The settings migration starts with the site locked down and in dev mode.
 */
function activateEvent(?App\Models\Event $event = null): App\Models\Event {
	$event ??= App\Models\Event::factory()->create();
	App\Models\Setting::set('active-event', $event);
	App\Models\Setting::set('lockdown', false);
	App\Models\Setting::set('dev-mode', false);
	return $event;
}

/**
 * Creates a user with a specific role
 */
function userWithRole(App\Models\Role $role, array $attributes = []): App\Models\User {
	return App\Models\User::factory()->create(['role' => $role, ...$attributes]);
}

/**
 * Gets the headers for an Inertia request, including the current asset version so it isn't rejected
 */
function inertiaHeaders(array $extra = []): array {
	return [
		'X-Inertia' => 'true',
		'X-Inertia-Version' => (string) app(App\Http\Middleware\HandleInertiaRequests::class)->version(request()),
		...$extra,
	];
}

/**
 * Replaces the Telegram facade with a mock and returns it.
 * The facade's BotsManager class is final, so the facade's shouldReceive() can't be used.
 */
function fakeTelegram(): Mockery\MockInterface {
	$mock = Mockery::mock();
	Telegram\Bot\Laravel\Facades\Telegram::swap($mock);
	return $mock;
}

/**
 * Creates a finished time entry for a user in a department, lasting a specific number of minutes
 */
function timeEntryFor(App\Models\User $user, App\Models\Department $department, Carbon\CarbonInterface $start, int $minutes, array $attributes = []): App\Models\TimeEntry {
	return App\Models\TimeEntry::factory()->create([
		'user_id' => $user->id,
		'event_id' => $department->event_id,
		'department_id' => $department->id,
		'start' => $start,
		'stop' => $start->avoidMutation()->addMinutes($minutes),
		'auto' => false,
		...$attributes,
	]);
}
