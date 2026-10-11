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
	$gone = User::factory()->create(['badge_id' => 300, 'role' => Role::Volunteer]);
	fakeConCat([conCatVolunteer(100, 'new'), conCatVolunteer(200, 'brandnew')]);

	$this->artisan('auth:sync-concat')->assertSuccessful();

	expect($user->fresh()->username)->toBe('old');
	expect(User::whereBadgeId(200)->exists())->toBeFalse();
	expect($gone->fresh()->role)->toBe(Role::Volunteer);
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

it('demotes volunteers and staff missing from ConCat and skips deleted users', function () {
	$goneStaff = User::factory()->create(['badge_id' => 100, 'first_name' => 'Keep', 'role' => Role::Staff]);
	$goneVolunteer = User::factory()->create(['badge_id' => 101, 'role' => Role::Volunteer]);
	$goneLead = User::factory()->create(['badge_id' => 102, 'role' => Role::Lead]);
	$goneBanned = User::factory()->create(['badge_id' => 103, 'role' => Role::Banned]);
	$deleted = User::factory()->create(['badge_id' => 200, 'username' => 'old']);
	$deleted->delete();
	fakeConCat([conCatVolunteer(200, 'new')]);

	$this->artisan('auth:sync-concat --apply')
		->expectsOutputToContain('not found in ConCat volunteers (2)')
		->assertSuccessful();

	expect($goneStaff->fresh()->role)->toBe(Role::Attendee)
		->and($goneStaff->fresh()->first_name)->toBe('Keep');
	expect($goneVolunteer->fresh()->role)->toBe(Role::Attendee);
	expect($goneLead->fresh()->role)->toBe(Role::Lead);
	expect($goneBanned->fresh()->role)->toBe(Role::Banned);
	expect(User::withTrashed()->whereBadgeId(200)->count())->toBe(1);
	expect(User::withTrashed()->whereBadgeId(200)->sole()->username)->toBe('old');
});
