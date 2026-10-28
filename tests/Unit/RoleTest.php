<?php

use App\Casts\JsonValue;
use App\Models\Role;
use App\Models\Setting;

it('finds roles by name, ignoring case', function (string $name, Role $role) {
	expect(Role::fromName($name))->toBe($role);
})->with([
	['admin', Role::Admin],
	['MANAGER', Role::Manager],
	['banned', Role::Banned],
]);

it('rejects unknown role names', function () {
	Role::fromName('overlord');
})->throws(ValueError::class, 'Unknown role name: Overlord');

it('has a description, color, and action label for every role', function (Role $role) {
	expect($role->description())->toBeString()->not->toBeEmpty()
		->and($role->colorClass())->toBeString()->not->toBeEmpty()
		->and($role->actionLabel())->toBeString()->not->toBeEmpty();
})->with(Role::cases());

it('orders roles from banned up to admin', function () {
	$values = array_map(fn (Role $role) => $role->value, [
		Role::Banned,
		Role::Attendee,
		Role::Volunteer,
		Role::Staff,
		Role::Lead,
		Role::Manager,
		Role::Admin,
	]);

	expect($values)->toBe([-2, -1, 0, 1, 2, 3, 4]);
});

it('stores setting values as JSON', function (mixed $value, string $json) {
	$cast = new JsonValue;
	$setting = new Setting;

	expect($cast->set($setting, 'value', $value, []))->toBe($json)
		->and($cast->get($setting, 'value', $json, []))->toBe($value);
})->with([
	[true, 'true'],
	[null, 'null'],
	['01a0edc0-18ad-7070-ba80-4df1b7ca2455', '"01a0edc0-18ad-7070-ba80-4df1b7ca2455"'],
	[['a' => 1], '{"a":1}'],
]);
