<?php

use App\Models\Reward;
use App\Models\TimeEntry;
use App\Models\User;
use App\Notifications\RewardAvailable;
use App\Notifications\RewardClaimed;
use App\Notifications\TelegramChannel;
use App\Notifications\TimeEntryAutoStopped;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->event = activateEvent();
	$this->reward = Reward::factory()->create([
		'event_id' => $this->event->id,
		'name' => 'Shirt <b>',
		'description' => 'A & B',
		'hours' => 8,
	]);
});

it('lists unread notifications and acknowledges them', function () {
	$user = User::factory()->telegramUnlinked()->create();
	$user->notify(new RewardAvailable($this->reward));
	$user->notify(new RewardClaimed($this->reward));

	$this->actingAs($user)
		->getJson(route('notifications.index'))
		->assertOk()
		->assertJsonCount(2, 'notifications')
		->assertJsonPath('notifications.0.reward_id', $this->reward->id);

	$this->actingAs($user)->postJson(route('notifications.acknowledge'))->assertStatus(205);

	expect($user->unreadNotifications()->count())->toBe(0);
	$this->actingAs($user->fresh())->getJson(route('notifications.index'))->assertJsonCount(0, 'notifications');
});

it('redirects home after acknowledging notifications from the page', function () {
	$this->actingAs(User::factory()->create())
		->post(route('notifications.acknowledge'))
		->assertRedirect(route('volunteer.index'));
});

it('escapes reward details in notifications', function () {
	$user = User::factory()->create();
	$available = (new RewardAvailable($this->reward))->toArray($user);
	$claimed = (new RewardClaimed($this->reward))->toArray($user);

	expect($available['description'])->toContain('<strong>8hr</strong> reward (Shirt &lt;b&gt;)')
		->toContain('A &amp; B')
		->and($available['type'])->toBe('success')
		->and($claimed['description'])->toContain('(Shirt &lt;b&gt;)')
		->and($claimed['type'])->toBe('info');
});

it('describes the time credited for auto-stopped time entries', function () {
	$entry = TimeEntry::factory()->create(['start' => now()->subHours(3), 'stop' => now()->subHours(2)]);
	$notification = new TimeEntryAutoStopped($entry);

	expect($notification->getCredited())->toBe('1 hour')
		->and($notification->toArray($entry->user)['description'])->toContain('<strong>1 hour</strong>')
		->and($notification->toTelegram($entry->user)['text'])->toContain('<u>1 hour</u>');
});

it('sends notifications through Telegram to users with a linked chat', function () {
	$user = User::factory()->create(['tg_chat_id' => 5555]);
	fakeTelegram()->shouldReceive('sendMessage')->once()->with(Mockery::on(
		fn (array $message) => $message['chat_id'] === 5555 && str_contains($message['text'], '8hr reward'),
	));

	(new TelegramChannel)->send($user, new RewardAvailable($this->reward));
});

it('skips Telegram for users without a linked chat', function () {
	$user = User::factory()->telegramUnlinked()->create();
	fakeTelegram()->shouldReceive('sendMessage')->never();

	(new TelegramChannel)->send($user, new RewardAvailable($this->reward));
});

it('stores notifications in the database and sends them to Telegram', function (string $class, mixed $subject) {
	$user = User::factory()->telegramLinked()->create();
	fakeTelegram()->shouldReceive('sendMessage')->once();

	$subject = $subject === 'reward' ? $this->reward : TimeEntry::factory()->create(['user_id' => $user->id]);
	$user->notify(new $class($subject));

	expect($user->notifications()->sole()->type)->toBe($class);
})->with([
	[RewardAvailable::class, 'reward'],
	[RewardClaimed::class, 'reward'],
	[TimeEntryAutoStopped::class, 'entry'],
]);
