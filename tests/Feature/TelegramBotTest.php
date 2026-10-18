<?php

use App\Events\QuickCodeGenerated;
use App\Models\Department;
use App\Models\QuickCode;
use App\Models\Reward;
use App\Models\RewardClaim;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Telegram\Commands\Command;
use App\Telegram\Commands\HelpCommand;
use App\Telegram\Commands\HoursCommand;
use App\Telegram\Commands\LinkCommand;
use App\Telegram\Commands\PlzHelpCommand;
use App\Telegram\Commands\QuickCodeCommand;
use App\Telegram\Commands\RewardsCommand;
use App\Telegram\Commands\UnlinkCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

uses(RefreshDatabase::class);

beforeEach(function () {
	$this->event = activateEvent(App\Models\Event::factory()->create(['name' => 'BLFC 2026']));
	$this->user = User::factory()->create(['badge_name' => 'Foxy', 'badge_id' => 1234, 'tg_chat_id' => 5555]);

	// Collect every message the bot sends
	$this->sent = collect();
	$this->api = Mockery::mock(Api::class);
	$this->api->shouldReceive('sendMessage', 'sendSticker')->andReturnUsing(function (array $params) {
		$this->sent->push($params);
		return new Message([]);
	});
	$this->api->shouldReceive('getCommands')->andReturn(collect([
		new HelpCommand,
		new HoursCommand,
		new LinkCommand,
		new PlzHelpCommand,
		new QuickCodeCommand,
		new RewardsCommand,
		new UnlinkCommand,
	])->keyBy(fn (Command $command) => $command->getName())->all());
});

/**
 * Runs a bot command as if it was sent in a chat, returning the text of the bot's last reply
 */
function sendTelegramCommand(Command $command, string $text, int $chatId = 5555): ?string {
	$test = test();
	$commandLength = strlen(explode(' ', $text)[0]);
	$entity = ['type' => 'bot_command', 'offset' => 0, 'length' => $commandLength];
	$update = new Update([
		'update_id' => 1,
		'message' => [
			'message_id' => 1,
			'date' => 0,
			'chat' => ['id' => $chatId, 'type' => 'private'],
			'text' => $text,
			'entities' => [$entity],
		],
	]);

	$command->make($test->api, $update, $entity);
	return $test->sent->last()['text'] ?? null;
}

it('shows the help for unlinked chats', function () {
	$reply = sendTelegramCommand(new HelpCommand, '/help', 9999);

	expect($reply)->toContain("<b>Ongoing event:</b>\nBLFC 2026")
		->toContain("You haven't linked a volunteer account to me.")
		->toContain('/link - Link your volunteer account')
		->toContain('/help - ')
		->not->toContain('/hours')
		->not->toContain('/plzhelp');
});

it('shows the help for linked chats', function () {
	Setting::set('active-event', null);

	$reply = sendTelegramCommand(new HelpCommand, '/help');

	expect($reply)->toContain('None 😔')
		->toContain('Foxy (#1234)')
		->toContain('/hours - Show hours clocked')
		->not->toContain('/link');
});

it('links a chat to a volunteer account with its setup key, then changes the key', function () {
	$user = User::factory()->telegramUnlinked()->create(['badge_name' => 'Wolfy']);
	$key = $user->tg_setup_key;

	$reply = sendTelegramCommand(new LinkCommand, "/start {$key}", 7777);

	$user->refresh();
	expect($reply)->toContain('Thanks for volunteering, <b>Wolfy</b>!')
		->and($user->tg_chat_id)->toBe(7777)
		->and($user->tg_setup_key)->not->toBe($key);
});

it('tells the old chat when a volunteer account is linked to a new one', function () {
	sendTelegramCommand(new LinkCommand, "/link {$this->user->tg_setup_key}", 7777);

	expect($this->sent)->toHaveCount(2)
		->and($this->sent[0]['chat_id'])->toBe(5555)
		->and($this->sent[0]['text'])->toContain('changed to report to another user')
		->and($this->user->fresh()->tg_chat_id)->toBe(7777);
});

it('still links a new chat when the old chat cannot be reached', function () {
	$api = Mockery::mock(Api::class);
	$api->shouldReceive('sendMessage')->once()->andThrow(new TelegramSDKException('Chat not found'));
	$api->shouldReceive('sendMessage')->once()->andReturn(new Message([]));
	$this->api = $api;

	sendTelegramCommand(new LinkCommand, "/link {$this->user->tg_setup_key}", 7777);

	expect($this->user->fresh()->tg_chat_id)->toBe(7777);
});

it('refuses to link without a valid setup key or from a chat that is already linked', function () {
	expect(sendTelegramCommand(new LinkCommand, '/link', 7777))->toContain("setup key for your volunteer account wasn't provided")
		->and(sendTelegramCommand(new LinkCommand, '/link ' . str_repeat('a', 32), 7777))->toContain('Unable to validate')
		->and(sendTelegramCommand(new LinkCommand, '/link ' . str_repeat('a', 32)))->toContain("I'm already familiar with you");
});

it('unlinks chats, even for banned volunteers', function () {
	$this->user->update(['role' => Role::Banned]);

	expect(sendTelegramCommand(new UnlinkCommand, '/unlink'))->toContain('Your volunteer account has been unlinked.')
		->and($this->user->fresh()->tg_chat_id)->toBeNull();
});

it('asks unlinked chats to link an account first', function () {
	expect(sendTelegramCommand(new HoursCommand, '/hours', 9999))->toContain("I don't have a volunteer account associated with you yet.");
});

it('refuses banned volunteers', function () {
	$this->user->update(['role' => Role::Banned]);

	expect(sendTelegramCommand(new HoursCommand, '/hours'))->toContain('There is a hold on your volunteer account.');
});

it('needs an active event for hours and rewards', function () {
	Setting::set('active-event', null);

	expect(sendTelegramCommand(new HoursCommand, '/hours'))->toContain("There isn't any ongoing event right now.")
		->and(sendTelegramCommand(new RewardsCommand, '/rewards'))->toContain("There isn't any ongoing event right now.");
});

it('shows the hours of a volunteer, including an ongoing shift', function () {
	$department = Department::factory()->create(['event_id' => $this->event->id]);
	timeEntryFor($this->user, $department, now()->subDays(2), 150);
	App\Models\TimeEntry::factory()->create([
		'user_id' => $this->user->id,
		'event_id' => $this->event->id,
		'department_id' => $department->id,
		'start' => now()->subMinutes(30),
		'stop' => null,
	]);

	$reply = sendTelegramCommand(new HoursCommand, '/hours');

	expect($reply)->toContain('<b><u>BLFC 2026</u></b>')
		->toContain("You're currently clocked in!")
		->toContain('<b>Shift time:</b> 30m')
		->toContain('<b>Total time earned:</b> 3h');
});

it('gives volunteers a quick sign-in code, replacing their old one', function () {
	EventFacade::fake([QuickCodeGenerated::class]);

	$first = sendTelegramCommand(new QuickCodeCommand, '/code');
	$code = QuickCode::sole()->code;
	expect($first)->toContain("<code>{$code}</code>")->not->toContain('old code');

	$second = sendTelegramCommand(new QuickCodeCommand, '/code');
	expect($second)->toContain('Your old code was invalidated.')
		->and(QuickCode::count())->toBe(1);
	EventFacade::assertDispatchedTimes(QuickCodeGenerated::class, 2);
});

it('does not give quick sign-in codes to leads and above', function () {
	$this->user->update(['role' => Role::Lead]);

	expect(sendTelegramCommand(new QuickCodeCommand, '/code'))->toContain('staff may not use quick sign-in codes')
		->and(QuickCode::count())->toBe(0);
});

it('lists rewards with whether they are claimed, available, or locked', function () {
	$department = Department::factory()->create(['event_id' => $this->event->id]);
	timeEntryFor($this->user, $department, now()->subDays(2), 9 * 60);
	$claimed = Reward::factory()->create(['event_id' => $this->event->id, 'hours' => 2, 'name' => 'Pin']);
	Reward::factory()->create(['event_id' => $this->event->id, 'hours' => 8, 'name' => 'Shirt']);
	Reward::factory()->create(['event_id' => $this->event->id, 'hours' => 12, 'name' => 'Hoodie & Hat']);
	$claim = new RewardClaim;
	$claim->user_id = $this->user->id;
	$claim->reward_id = $claimed->id;
	$claim->save();

	$reply = sendTelegramCommand(new RewardsCommand, '/rewards');

	expect($reply)->toContain('✅ <u><b>2hr:</b> Pin</u>')
		->toContain('⭐ <u><b>8hr:</b> Shirt</u>')
		->toContain('⏳ <u><b>12hr:</b> Hoodie &amp; Hat</u>');
});

it('says when an event has no rewards', function () {
	expect(sendTelegramCommand(new RewardsCommand, '/rewards'))->toBe('There are no rewards available for BLFC 2026.');
});

it('replies to pleas for help with stickers', function () {
	sendTelegramCommand(new PlzHelpCommand, '/plzhelp');

	expect($this->sent)->toHaveCount(2)
		->and($this->sent->pluck('sticker')->filter())->toHaveCount(2);
});

it('passes webhook updates to the command handler', function () {
	fakeTelegram()->shouldReceive('commandsHandler')->once()->with(true);

	$this->post(route('telegram.tracker.webhook'))->assertOk()->assertSee('ok');
});
