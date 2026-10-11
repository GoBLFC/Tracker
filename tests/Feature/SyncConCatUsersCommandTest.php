<?php

use App\Facades\ConCat;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function conCatVolunteer(int $id, string $username, bool $isStaff = false): object {
	return (object) [
		'user' => (object) [
			'id' => (string) $id,
			'username' => $username,
			'firstName' => 'First' . $id,
			'lastName' => 'Last' . $id,
			'isStaff' => $isStaff,
		],
	];
}

function conCatRegistration(int $id, string $badgeName): object {
	return (object) ['badgeName' => $badgeName, 'user' => (object) ['id' => (string) $id]];
}

function fakeConCat(array $volunteers, array $registrations = []): void {
	ConCat::shouldReceive('authorize')->andReturnSelf();
	ConCat::shouldReceive('searchVolunteers')->andReturn($volunteers);
	ConCat::shouldReceive('searchRegistrationsByUserIds')
		->withArgs(fn ($ids) => collect($ids)->every(fn ($id) => is_string($id)))
		->andReturn($registrations);
}

it('changes nothing without --apply', function () {
	$user = User::factory()->create(['badge_id' => 100, 'username' => 'old']);
	fakeConCat([conCatVolunteer(100, 'new'), conCatVolunteer(200, 'brandnew')]);

	$this->artisan('auth:sync-concat')->assertSuccessful();

	expect($user->fresh()->username)->toBe('old');
	expect(User::whereBadgeId(200)->exists())->toBeFalse();
});

it('refreshes usernames and badge names, creates missing volunteers, and promotes staff', function () {
	$existing = User::factory()->create(['badge_id' => 100, 'username' => 'old', 'first_name' => 'Keep', 'last_name' => 'Me', 'role' => Role::Volunteer]);
	$attendee = User::factory()->attendee()->create(['badge_id' => 300]);
	fakeConCat(
		[conCatVolunteer(100, 'new', isStaff: true), conCatVolunteer(200, 'brandnew'), conCatVolunteer(300, 'att')],
		[conCatRegistration(100, 'Fluffy'), conCatRegistration(200, 'Scales')],
	);

	$this->artisan('auth:sync-concat --apply')->assertSuccessful();

	$existing->refresh();
	expect($existing->username)->toBe('new')
		->and($existing->first_name)->toBe('Keep')
		->and($existing->last_name)->toBe('Me')
		->and($existing->badge_name)->toBe('Fluffy')
		->and($existing->role)->toBe(Role::Staff);

	$created = User::whereBadgeId(200)->sole();
	expect($created->username)->toBe('brandnew')
		->and($created->first_name)->toBe('Unidentified')
		->and($created->last_name)->toBe('Volunteer')
		->and($created->badge_name)->toBe('Scales')
		->and($created->role)->toBe(Role::Volunteer);

	expect($attendee->fresh()->role)->toBe(Role::Volunteer);
});

it('never changes banned or higher roles', function () {
	$banned = User::factory()->create(['badge_id' => 100, 'role' => Role::Banned]);
	$manager = User::factory()->create(['badge_id' => 200, 'role' => Role::Manager]);
	fakeConCat([conCatVolunteer(100, 'a', isStaff: true), conCatVolunteer(200, 'b', isStaff: true)]);

	$this->artisan('auth:sync-concat --apply')->assertSuccessful();

	expect($banned->fresh()->role)->toBe(Role::Banned);
	expect($manager->fresh()->role)->toBe(Role::Manager);
});

it('only reports users missing from ConCat and skips deleted users', function () {
	$gone = User::factory()->create(['badge_id' => 100, 'role' => Role::Staff]);
	$deleted = User::factory()->create(['badge_id' => 200, 'username' => 'old']);
	$deleted->delete();
	fakeConCat([conCatVolunteer(200, 'new')]);

	$this->artisan('auth:sync-concat --apply')
		->expectsOutputToContain('not found in ConCat volunteers (1)')
		->assertSuccessful();

	expect($gone->fresh()->role)->toBe(Role::Staff);
	expect(User::withTrashed()->whereBadgeId(200)->count())->toBe(1);
	expect(User::withTrashed()->whereBadgeId(200)->sole()->username)->toBe('old');
});
