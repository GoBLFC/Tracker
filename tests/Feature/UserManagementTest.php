<?php

use App\Facades\ConCat;
use App\Models\Department;
use App\Models\Role;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->event = activateEvent();
	$this->manager = userWithRole(Role::Manager, ['badge_name' => 'Manager']);
	$this->admin = userWithRole(Role::Admin, ['badge_name' => 'Admin']);
});

it('only lets managers list users', function () {
	$this->actingAs(User::factory()->create())->getJson(route('users.index'))->assertForbidden();
	$this->actingAs($this->manager)->getJson(route('users.index'))->assertOk()->assertJsonPath('total', 3);
});

it('filters users by name, badge ID, and role', function () {
	$foxy = User::factory()->create(['badge_name' => 'Foxy McFoxface', 'badge_id' => 1111]);
	User::factory()->create(['badge_name' => 'Wolfy', 'badge_id' => 2222]);

	$this->actingAs($this->manager)
		->getJson(route('users.index', ['name' => 'mcfox']))
		->assertJsonPath('total', 1)
		->assertJsonPath('users.0.id', $foxy->id);

	$this->actingAs($this->manager)
		->getJson(route('users.index', ['badge_id' => 2222]))
		->assertJsonPath('total', 1)
		->assertJsonPath('users.0.badge_name', 'Wolfy')
		->assertJsonPath('filters.badge_id', 2222);

	$this->actingAs($this->manager)
		->getJson(route('users.index', ['role' => Role::Admin->value]))
		->assertJsonPath('total', 1)
		->assertJsonPath('users.0.id', $this->admin->id);
});

it('sorts users by display name by default and by other fields when asked', function () {
	User::factory()->create(['badge_name' => 'Zebra', 'badge_id' => 1]);
	User::factory()->create(['badge_name' => 'Aardvark', 'badge_id' => 99999999]);

	$this->actingAs($this->manager)
		->getJson(route('users.index'))
		->assertJsonPath('users.0.badge_name', 'Aardvark')
		->assertJsonPath('sortBy', 'display_name')
		->assertJsonPath('sortDir', 'asc');

	$this->actingAs($this->manager)
		->getJson(route('users.index', ['sortBy' => 'badge_id', 'sortDir' => 'desc']))
		->assertJsonPath('users.0.badge_name', 'Aardvark')
		->assertJsonPath('users.3.badge_name', 'Zebra');
});

it('paginates users', function () {
	User::factory()->count(3)->create();

	$this->actingAs($this->manager)
		->getJson(route('users.index', ['count' => 2, 'page' => 2]))
		->assertJsonPath('total', 5)
		->assertJsonPath('perPage', 2)
		->assertJsonPath('first', 3)
		->assertJsonCount(2, 'users');
});

it('validates the user list parameters', function (array $params, string $field) {
	$this->actingAs($this->manager)
		->getJson(route('users.index', $params))
		->assertUnprocessable()
		->assertJsonValidationErrors($field);
})->with([
	[['sortBy' => 'first_name'], 'sortBy'],
	[['sortDir' => 'sideways'], 'sortDir'],
	[['count' => 51], 'count'],
	[['role' => 5], 'role'],
]);

it('lets managers create users from a badge ID with their ConCat details', function () {
	ConCat::shouldReceive('authorizeIfNeeded');
	ConCat::shouldReceive('getRegistration')->once()->with(1234)->andReturn((object) [
		'badgeName' => 'Foxy',
		'user' => (object) ['id' => 1234, 'username' => 'foxy', 'firstName' => 'Fox', 'lastName' => 'Face'],
	]);

	$this->actingAs($this->manager)
		->postJson(route('users.store'), ['badge_id' => 1234])
		->assertOk()
		->assertJsonPath('user.badge_name', 'Foxy');

	$user = User::whereBadgeId(1234)->sole();
	expect($user->role)->toBe(Role::Volunteer)
		->and($user->username)->toBe('foxy')
		->and($user->full_name)->toBe('Fox Face');
});

it('falls back to the ConCat user when the badge has no registration', function () {
	ConCat::shouldReceive('authorizeIfNeeded');
	ConCat::shouldReceive('getRegistration')->andThrow(new RuntimeException('Not found'));
	ConCat::shouldReceive('getUser')->once()->with(1234)->andReturn(
		(object) ['id' => 1234, 'username' => 'foxy', 'firstName' => 'Fox', 'lastName' => 'Face'],
	);

	$this->actingAs($this->manager)->postJson(route('users.store'), ['badge_id' => 1234])->assertOk();

	$user = User::whereBadgeId(1234)->sole();
	expect($user->username)->toBe('foxy')
		->and($user->badge_name)->toBeNull();
});

it('creates a placeholder user when ConCat is unavailable', function () {
	ConCat::shouldReceive('authorizeIfNeeded')->andThrow(new RuntimeException('ConCat is down'));

	$this->actingAs($this->manager)->postJson(route('users.store'), ['badge_id' => 1234])->assertOk();

	$user = User::whereBadgeId(1234)->sole();
	expect($user->username)->toBe('unknown')
		->and($user->first_name)->toBe('Unidentified')
		->and($user->last_name)->toBe('Volunteer');
});

it('validates new badge IDs', function (mixed $badgeId) {
	User::factory()->create(['badge_id' => 1234]);

	$this->actingAs($this->manager)
		->postJson(route('users.store'), ['badge_id' => $badgeId])
		->assertUnprocessable()
		->assertJsonValidationErrors('badge_id');
})->with([1234, 123456789, 'abc', null]);

it('does not let volunteers create users', function () {
	$this->actingAs(User::factory()->create())
		->postJson(route('users.store'), ['badge_id' => 1234])
		->assertForbidden();
});

it('lets admins update users', function () {
	$user = User::factory()->create();

	$this->actingAs($this->admin)
		->patchJson(route('users.update', $user), ['badge_name' => 'New Name', 'role' => Role::Lead->value])
		->assertOk()
		->assertJsonPath('user.badge_name', 'New Name');

	expect($user->fresh()->role)->toBe(Role::Lead);
});

it('does not let managers update users', function () {
	$user = User::factory()->create();

	$this->actingAs($this->manager)
		->patchJson(route('users.update', $user), ['badge_name' => 'New Name'])
		->assertForbidden();
});

it('does not let users change their own role', function () {
	$this->actingAs($this->admin)
		->patchJson(route('users.update', $this->admin), ['role' => Role::Volunteer->value])
		->assertForbidden()
		->assertJsonPath('error', 'Cannot change your own role.');

	expect($this->admin->fresh()->role)->toBe(Role::Admin);
});

it('rejects unknown roles', function () {
	$this->actingAs($this->admin)
		->patchJson(route('users.update', User::factory()->create()), ['role' => 9])
		->assertUnprocessable()
		->assertJsonValidationErrors('role');
});

it('lets admins delete other users, but not themselves', function () {
	$user = User::factory()->create();

	$this->actingAs($this->manager)->deleteJson(route('users.destroy', $user))->assertForbidden();
	$this->actingAs($this->admin)
		->deleteJson(route('users.destroy', $this->admin))
		->assertForbidden()
		->assertJsonPath('error', 'Cannot delete yourself.');
	$this->actingAs($this->admin)->deleteJson(route('users.destroy', $user))->assertStatus(205);

	expect($user->fresh()->trashed())->toBeTrue();
});

it('searches users by name or badge ID, including their ongoing time entries', function () {
	$department = Department::factory()->create(['event_id' => $this->event->id]);
	$foxy = User::factory()->create(['badge_name' => 'Foxy', 'badge_id' => 4321]);
	TimeEntry::factory()->ongoing()->create([
		'user_id' => $foxy->id,
		'event_id' => $this->event->id,
		'department_id' => $department->id,
	]);

	$this->actingAs($this->manager)
		->getJson(route('users.search', ['q' => 'FOX']))
		->assertOk()
		->assertJsonCount(1, 'users')
		->assertJsonPath('users.0.id', $foxy->id)
		->assertJsonPath('users.0.time_entries.0.department.id', $department->id);

	$this->actingAs($this->manager)
		->getJson(route('users.search', ['q' => '4321']))
		->assertJsonPath('users.0.id', $foxy->id);
});

it('only lets managers search users', function () {
	$this->actingAs(User::factory()->create())
		->getJson(route('users.search', ['q' => 'fox']))
		->assertForbidden();
});
