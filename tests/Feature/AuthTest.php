<?php

use App\Events\QuickCodeLogin;
use App\Models\Kiosk;
use App\Models\QuickCode;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Inertia\Testing\AssertableInertia;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\OAuth2\User as OAuthUser;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->withoutVite();
	activateEvent();
});

/**
 * Creates a quick code for a user
 */
function quickCodeFor(User $user, ?string $code = null): QuickCode {
	$quickCode = new QuickCode;
	$quickCode->user_id = $user->id;
	if ($code) $quickCode->code = $code;
	$quickCode->save();
	return $quickCode;
}

/**
 * Builds an OAuth user like the ConCat Socialite provider returns
 */
function fakeOAuthUser(int $id, array $raw = []): OAuthUser {
	$raw = ['id' => $id, 'username' => "user{$id}", 'firstName' => 'Test', 'lastName' => 'User', 'badgeName' => "Badge {$id}", ...$raw];
	return (new OAuthUser)->setRaw($raw)->map(['id' => $id, 'nickname' => $raw['username']])->setToken('oauth-token');
}

it('redirects guests to the login page', function () {
	$this->get(route('volunteer.index'))->assertRedirect(route('auth.login'));
});

it('shows the login page to guests only', function () {
	$this->get(route('auth.login'))
		->assertOk()
		->assertInertia(fn (AssertableInertia $page) => $page->component('Login'));

	$this->actingAs(User::factory()->create())
		->get(route('auth.login'))
		->assertRedirect(route('volunteer.index'));
});

it('redirects to ConCat to start the OAuth flow', function () {
	Socialite::shouldReceive('driver->scopes->redirect')->andReturn(redirect('https://concat.test/oauth/authorize'));

	$this->get(route('auth.redirect'))->assertRedirect('https://concat.test/oauth/authorize');
});

it('creates a volunteer from the OAuth callback and logs them in', function () {
	Socialite::shouldReceive('driver->user')->andReturn(fakeOAuthUser(1234));

	$this->get(route('auth.callback'))
		->assertRedirect('/')
		->assertSessionHas('conCatToken', 'oauth-token');

	$user = User::whereBadgeId(1234)->firstOrFail();
	expect($user->username)->toBe('user1234')
		->and($user->badge_name)->toBe('Badge 1234')
		->and($user->fresh()->role)->toBe(Role::Volunteer);
	$this->assertAuthenticatedAs($user);
});

it('updates existing users from the OAuth callback and promotes attendees to volunteers', function () {
	$user = User::factory()->create(['badge_id' => 1234, 'role' => Role::Attendee, 'badge_name' => 'Old Name']);
	Socialite::shouldReceive('driver->user')->andReturn(fakeOAuthUser(1234, ['badgeName' => 'New Name']));

	$this->get(route('auth.callback'))->assertRedirect();

	$user->refresh();
	expect($user->badge_name)->toBe('New Name')
		->and($user->role)->toBe(Role::Volunteer)
		->and(User::count())->toBe(1);
});

it('does not change the role of existing users above attendee in the OAuth callback', function () {
	$user = userWithRole(Role::Manager, ['badge_id' => 1234]);
	Socialite::shouldReceive('driver->user')->andReturn(fakeOAuthUser(1234));

	$this->get(route('auth.callback'))->assertRedirect();

	expect($user->fresh()->role)->toBe(Role::Manager);
});

it('logs in with a quick code, ignoring case, and only once', function () {
	EventFacade::fake([QuickCodeLogin::class]);
	$user = User::factory()->create();
	quickCodeFor($user, 'AB12');

	$this->postJson(route('auth.quickcode.post'), ['code' => 'ab12'])->assertStatus(205);

	$this->assertAuthenticatedAs($user);
	expect(QuickCode::count())->toBe(0);
	EventFacade::assertDispatched(QuickCodeLogin::class);
});

it('redirects to the volunteer page after a quick code login from a form', function () {
	$user = User::factory()->create();
	quickCodeFor($user, 'AB12');

	$this->post(route('auth.quickcode.post'), ['code' => 'AB12'])->assertRedirect(route('volunteer.index'));
});

it('rejects unknown and expired quick codes', function () {
	$user = User::factory()->create();
	quickCodeFor($user, 'AB12');

	$this->postJson(route('auth.quickcode.post'), ['code' => 'ZZ99'])
		->assertUnauthorized()
		->assertJsonPath('errors', 'Quick code not recognized.');

	$this->travel(31)->seconds();
	$this->postJson(route('auth.quickcode.post'), ['code' => 'AB12'])->assertUnauthorized();

	$this->from(route('auth.login'))
		->post(route('auth.quickcode.post'), ['code' => 'AB12'])
		->assertRedirect(route('auth.login'))
		->assertSessionHasErrors('code');
	$this->assertGuest();
});

it('validates the format of quick codes', function (string $code) {
	$this->postJson(route('auth.quickcode.post'), ['code' => $code])
		->assertUnprocessable()
		->assertJsonValidationErrors('code');
})->with(['ABC', 'ABCDE', 'AB-1']);

it('rate limits failed quick code attempts', function () {
	$user = User::factory()->create();
	quickCodeFor($user, 'AB12');

	foreach (range(1, 5) as $_) {
		$this->postJson(route('auth.quickcode.post'), ['code' => 'ZZ99'])->assertUnauthorized();
	}

	// Even a valid code is refused once the limit is reached
	$this->postJson(route('auth.quickcode.post'), ['code' => 'AB12'])->assertTooManyRequests();
	$this->assertGuest();
});

it('logs out and returns to the login page', function () {
	$this->actingAs(User::factory()->create())
		->get(route('auth.logout'))
		->assertRedirect(route('auth.login'));
	$this->assertGuest();

	$this->get(route('auth.logout'))->assertRedirect(route('auth.login'));
});

it('logs kiosk sessions out of ConCat too', function () {
	config(['services.concat.instance_uri' => 'https://concat.test', 'services.concat.client_id' => 'client']);
	$kiosk = Kiosk::create();

	$this->actingAs(User::factory()->create())
		->withSession(['conCatToken' => 'oauth-token'])
		->withCookie('kiosk', $kiosk->session_key)
		->get(route('auth.logout'))
		->assertRedirect('https://concat.test/oauth/logout?client_id=client&access_token=oauth-token&next=' . urlencode(route('auth.login')));
});

it('does not log out of ConCat outside of kiosks or in dev mode', function () {
	$kiosk = Kiosk::create();

	$this->actingAs(User::factory()->create())
		->withSession(['conCatToken' => 'oauth-token'])
		->get(route('auth.logout'))
		->assertRedirect(route('auth.login'));

	Setting::set('dev-mode', true);
	$this->actingAs(User::factory()->create())
		->withSession(['conCatToken' => 'oauth-token'])
		->withCookie('kiosk', $kiosk->session_key)
		->get(route('auth.logout'))
		->assertRedirect(route('auth.login'));
});

it('sends banned users to the suspension notice', function () {
	$banned = userWithRole(Role::Banned);

	$this->actingAs($banned)->get(route('volunteer.index'))->assertRedirect(route('auth.banned'));
	$this->actingAs($banned)
		->get(route('auth.banned'))
		->assertOk()
		->assertInertia(fn (AssertableInertia $page) => $page->component('Suspended'));
});

it('sends users that are not banned away from the suspension notice', function () {
	$this->actingAs(User::factory()->create())
		->get(route('auth.banned'))
		->assertRedirect(route('volunteer.index'));
});

it('denies banned users through every gate', function () {
	$banned = userWithRole(Role::Banned);
	expect($banned->can('viewAny', User::class))->toBeFalse();
});
