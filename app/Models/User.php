<?php

namespace App\Models;

use App\Notifications\RewardAvailable;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SocialiteProviders\Manager\OAuth2\User as OauthUser;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable {
	use HasFactory, Notifiable, SoftDeletes;

	public $incrementing = false;

	protected $casts = [
		'role' => Role::class,
	];

	protected $fillable = [
		'id',
		'username',
		'first_name',
		'last_name',
		'badge_name',
	];

	protected $hidden = [
		'tg_setup_key',
	];

	protected static function boot() {
		parent::boot();

		// Add a listener for the model being created to add a random tg_setup_key if one hasn't already been specified
		static::creating(function ($model) {
			if (!isset($model->tg_setup_key)) $model->generateTelegramSetupKey();
		});
	}

	/**
	 * Get the time entries associated with the user
	 */
	public function timeEntries(): HasMany {
		return $this->hasMany(TimeEntry::class);
	}

	/**
	 * Get the reward claims the user has made
	 */
	public function rewardClaims(): HasMany {
		return $this->hasMany(RewardClaim::class);
	}

	/**
	 * Get the quick codes the user can log in with
	 */
	public function quickCodes(): HasMany {
		return $this->hasMany(QuickCode::class);
	}

	/**
	 * Get all the departments the user has entered time for
	 */
	public function departments(): HasManyThrough {
		return $this->hasManyThrough(Department::class, TimeEntry::class);
	}

	/**
	 * Scope a query to only include users of a given role
	 */
	public function scopeOfRole(Builder $query, Role $role): void {
		$query->where('role', $role->value);
	}

	/**
	 * Check whether the user should have access to admin features
	 */
	public function isAdmin(): bool {
		return $this->role->value >= Role::Admin->value;
	}

	/**
	 * Check whether the user should have access to manager features
	 */
	public function isManager(): bool {
		return $this->role->value >= Role::Manager->value;
	}

	/**
	 * Check whether the user should have access to lead features
	 */
	public function isLead(): bool {
		return $this->role->value >= Role::Lead->value;
	}

	/**
	 * Check whether the user has been banned
	 */
	public function isBanned(): bool {
		return $this->role->value === Role::Banned->value;
	}

	/**
	 * Get the user's display name (badge name if available, username otherwise)
	 */
	public function getDisplayName(): string {
		return $this->badge_name ?? $this->username;
	}

	/**
	 * Get the total earned time (in seconds) for an event
	 * @param ?Event $event Event to get the earned time for - if null, then the active event will be used
	 * @param ?Collection $timeEntries Time entries to look through (to avoid extra queries if already retrieved)
	 */
	public function getEarnedTime(Event $event = null, Collection $timeEntries = null): int {
		if (!$event) $event = Setting::activeEvent();

		// Get all of the time entries from the user for the given event, along with the time bonuses that may apply
		if (!$timeEntries) $timeEntries = $this->timeEntries()->with(['department.timeBonuses'])->forEvent($event)->get();
		$bonuses = $timeEntries->pluck('department.timeBonuses')->flatten()->unique('id');

		// Add up the duration and bonus time of all time entries to get the total time for the event
		return $timeEntries->reduce(
			fn (?int $carry, TimeEntry $entry) => $carry + $entry->calculateTotalTime($bonuses),
			0
		);
	}

	/**
	 * Get statistics about the time spent
	 * @param ?Event $event Event to get the statistics for - if null, then the active event will be used
	 * @param ?Carbon $date Day for the day-specific statistics - if null, then today will be used
	 * @return array{total: int, day: int, entries: Collection<TimeEntry>, bonuses: Collection<TimeBonus>}
	 */
	public function getTimeStats(Event $event = null, Carbon $date = null): array {
		if (!$event) $event = Setting::activeEvent();
		if (!$date) $date = now();

		// Get the date offset by the day boundary hour for day comparisons later
		$boundaryHour = config('tracker.day_boundary_hour');
		$offsetDate = $date->avoidMutation()->subHours($boundaryHour);

		// Get all of the time entries from the user for the given event, along with the time bonuses that may apply
		$timeEntries = $this->timeEntries()->with(['department.timeBonuses'])->forEvent($event)->get();
		$bonuses = $timeEntries->pluck('department.timeBonuses')->flatten()->unique('id');

		// Add up the duration and bonus time of all time entries to get the total time for the event
		$totalTime = $timeEntries->reduce(
			fn (?int $carry, TimeEntry $entry) => $carry + $entry->calculateTotalTime($bonuses),
			0
		);

		// Narrow down the time entries to ones that interact with the given date, then get the sum of them all
		// while taking into account only the time that crosses the day boundary if applicable
		$dayTime = $timeEntries->filter(
			fn (TimeEntry $entry) =>
			$entry->getBoundaryOffsetStart(-1, $boundaryHour)->isSameDay($offsetDate) ||
				$entry->getBoundaryOffsetStop(-1, $boundaryHour)->isSameDay($offsetDate)
		)->reduce(
			fn (?int $carry, TimeEntry $entry) => $carry +
				(!$entry->isCrossingDayBoundary($boundaryHour)
					? $entry->getDuration()
					: $entry->getSecondsPastDayBoundary()
				),
			0
		);

		return [
			'total' => $totalTime,
			'day' => $dayTime,
			'entries' => $timeEntries,
			'bonuses' => $bonuses,
		];
	}

	/**
	 * Get applicable rewards, claims, eligible/claimed rewards, and earned hours for an event
	 * @param ?Event $event Event to get the reward info for - if null, then the active event will be used
	 * @param ?Collection $timeEntries Time entries to look through (to avoid extra queries if already retrieved)
	 * @return array{rewards: Collection<Reward>, eligible: Collection<Reward>, claimed: Collection<Reward>, claims: Collection<RewardClaim>, earnedHours: float}
	 */
	public function getRewardInfo(?Event $event = null, Collection $timeEntries = null): array {
		if (!$event) $event = Setting::activeEvent();
		$earnedHours = $this->getEarnedTime($event, $timeEntries) / 60 / 60;
		return [
			'rewards' => $event->rewards,
			'eligible' => $event->rewards->filter(fn (Reward $reward) => $earnedHours > $reward->hours),
			'claimed' => $event->rewards->filter(fn (Reward $reward) => $this->rewardClaims->contains('reward_id', $reward->id)),
			'claims' => $this->rewardClaims->where('event_id', $event->id),
			'earnedHours' => $earnedHours,
		];
	}

	/**
	 * Check whether the user has been notified of a reward being available
	 * @param Reward $reward
	 * @param ?Collection $notifications Notifications to look through (to avoid extra queries if already retrieved)
	 */
	public function hasBeenNotifiedForEligibleReward(Reward $reward, Collection $notifications = null): bool {
		if (!$notifications) $notifications = $this->notifications()->whereType(RewardAvailable::class)->get();
		return $notifications->contains('data.reward_id', $reward->id);
	}

	/**
	 * Get a URL to start interacting with the Telegram bot
	 */
	public function getTelegramSetupUrl(): string {
		$bot = Cache::remember('telegram-bot', 60 * 15, fn () => Telegram::getMe());
		return "https://t.me/{$bot->username}?start={$this->tg_setup_key}";
	}

	/**
	 * Generates and assigns a new Telegram setup key (tg_setup_key)
	 */
	public function generateTelegramSetupKey(): void {
		$this->tg_setup_key = Str::random(32);
	}

	/**
	 * Finds an existing user record and updates it with the latest information from an OAuth user, or creates a new
	 * record for it entirely.
	 */
	public static function updateOrCreateFromOauthUser(OauthUser $user): static {
		return static::updateOrCreate(['id' => $user->id], [
			'username' => $user->nickname,
			'first_name' => $user->user['firstName'],
			'last_name' => $user->user['lastName'],
			'badge_name' => $user->user['badgeName'],
		]);
	}
}
