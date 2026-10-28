<?php

use App\Models\Event;
use App\Models\Reward;
use App\Models\RewardClaim;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->event = activateEvent();
	$this->admin = userWithRole(Role::Admin);
	$this->manager = userWithRole(Role::Manager);
	$this->reward = Reward::factory()->create(['event_id' => $this->event->id, 'name' => 'Shirt', 'hours' => 8]);
});

it('lists the rewards of an event', function () {
	$this->actingAs(userWithRole(Role::Volunteer))
		->getJson(route('events.rewards.index', $this->event))
		->assertOk()
		->assertJsonPath('rewards.0.name', 'Shirt');
});

it('lets admins create rewards with names unique to the event', function () {
	$this->actingAs($this->admin)
		->postJson(route('events.rewards.store', $this->event), ['name' => 'Hoodie', 'description' => 'Warm', 'hours' => 16])
		->assertOk()
		->assertJsonPath('reward.event_id', $this->event->id);

	$this->actingAs($this->admin)
		->postJson(route('events.rewards.store', $this->event), ['name' => 'Shirt', 'description' => 'Again', 'hours' => 4])
		->assertUnprocessable()
		->assertJsonValidationErrors('name');
});

it('validates reward hours', function (mixed $hours) {
	$this->actingAs($this->admin)
		->postJson(route('events.rewards.store', $this->event), ['name' => 'Hoodie', 'description' => 'Warm', 'hours' => $hours])
		->assertUnprocessable()
		->assertJsonValidationErrors('hours');
})->with([-1, 169, 1.5, null]);

it('lets admins update and delete rewards', function () {
	$this->actingAs($this->admin)
		->patchJson(route('rewards.update', $this->reward), ['hours' => 10])
		->assertOk();
	expect($this->reward->fresh()->hours)->toEqual(10);

	$this->actingAs($this->admin)->deleteJson(route('rewards.destroy', $this->reward))->assertStatus(205);
	expect($this->reward->fresh()->trashed())->toBeTrue();
});

it('does not let managers change rewards', function () {
	$this->actingAs($this->manager)
		->postJson(route('events.rewards.store', $this->event), ['name' => 'Hoodie', 'description' => 'Warm', 'hours' => 16])
		->assertForbidden();
	$this->actingAs($this->manager)->patchJson(route('rewards.update', $this->reward), ['hours' => 1])->assertForbidden();
	$this->actingAs($this->manager)->deleteJson(route('rewards.destroy', $this->reward))->assertForbidden();
});

it('shows rewards of the active event to volunteers', function () {
	$volunteer = userWithRole(Role::Volunteer);
	$this->actingAs($volunteer)->getJson(route('rewards.show', $this->reward))->assertOk();
	$this->actingAs($volunteer)->getJson(route('rewards.show', Reward::factory()->create()))->assertForbidden();
});

it('lets managers claim rewards of the active event for volunteers', function () {
	$volunteer = User::factory()->create();

	$this->actingAs($this->manager)
		->putJson(route('users.claims.store', $volunteer), ['reward_id' => $this->reward->id])
		->assertOk()
		->assertJsonPath('reward_claim.reward_id', $this->reward->id);

	$this->actingAs($this->manager)
		->putJson(route('users.claims.store', $volunteer), ['reward_id' => $this->reward->id])
		->assertUnprocessable()
		->assertJsonPath('error', 'Reward is already claimed by user.');

	expect($volunteer->rewardClaims()->count())->toBe(1);
});

it('only lets admins claim rewards of other events', function () {
	$volunteer = User::factory()->create();
	$oldReward = Reward::factory()->create();

	$this->actingAs($this->manager)
		->putJson(route('users.claims.store', $volunteer), ['reward_id' => $oldReward->id])
		->assertForbidden();
	$this->actingAs($this->admin)
		->putJson(route('users.claims.store', $volunteer), ['reward_id' => $oldReward->id])
		->assertOk();
});

it('does not let volunteers claim rewards', function () {
	$volunteer = User::factory()->create();

	$this->actingAs($volunteer)
		->putJson(route('users.claims.store', $volunteer), ['reward_id' => $this->reward->id])
		->assertForbidden();
});

it('lets volunteers view their own claims and managers view anyone\'s', function () {
	$volunteer = User::factory()->create();
	$claim = new RewardClaim;
	$claim->user_id = $volunteer->id;
	$claim->reward_id = $this->reward->id;
	$claim->save();

	$this->actingAs($volunteer)
		->getJson(route('users.claims', $volunteer))
		->assertOk()
		->assertJsonPath('reward_claims.0.id', $claim->id);
	$this->actingAs(User::factory()->create())->getJson(route('users.claims', $volunteer))->assertForbidden();
	$this->actingAs($this->manager)->getJson(route('users.claims', $volunteer))->assertOk();

	// Claims are filtered by event
	$this->actingAs($this->manager)
		->getJson(route('users.claims.event', [$volunteer, Event::factory()->create()]))
		->assertJsonCount(0, 'reward_claims');
});

it('lets managers unclaim rewards of the active event only', function () {
	$volunteer = User::factory()->create();
	$claims = collect([$this->reward, Reward::factory()->create()])->map(function (Reward $reward) use ($volunteer) {
		$claim = new RewardClaim;
		$claim->user_id = $volunteer->id;
		$claim->reward_id = $reward->id;
		$claim->save();
		return $claim;
	});

	$this->actingAs($this->manager)->deleteJson(route('claims.destroy', $claims[1]))->assertForbidden();
	$this->actingAs($this->manager)->deleteJson(route('claims.destroy', $claims[0]))->assertStatus(205);
	$this->actingAs($this->admin)->deleteJson(route('claims.destroy', $claims[1]))->assertStatus(205);

	expect(RewardClaim::count())->toBe(0);
});
