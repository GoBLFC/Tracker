<?php

namespace App\Models;

use App\Facades\ConCat;
use App\Models\Contracts\HasAuditName;
use App\Models\Contracts\HasDisplayName;
use App\Notifications\RewardAvailable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SocialiteProviders\Manager\OAuth2\User as OAuthUser;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Telegram\Bot\Laravel\Facades\Telegram;

/**
 * @property int $badge_id
 * @property string $username
 * @property string $first_name
 * @property string $last_name
 * @property string|null $badge_name
 * @property \App\Models\Role $role
 * @property string $tg_setup_key
 * @property int|null $tg_chat_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $full_name
 * @property-read string $display_name
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\TimeEntry>|\App\Models\TimeEntry[] $timeEntries
 * @property-read int|null $time_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\RewardClaim>|\App\Models\RewardClaim[] $rewardClaims
 * @property-read int|null $reward_claims_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\QuickCode>|\App\Models\QuickCode[] $quickCodes
 * @property-read int|null $quick_codes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Department>|\App\Models\Department[] $departments
 * @property-read int|null $departments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\AttendeeLog>|\App\Models\AttendeeLog[] $attendeeLogs
 * @property-read int|null $attendee_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Activity>|\App\Models\Activity[] $activities
 * @property-read int|null $activities_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User ofRole(\App\Models\Role $role)
 * @method static \Database\Factories\UserFactory<self> factory($count = null, $state = [])
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\User firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\User firstOrFail($columns = ['*'])
 * @method \App\Models\User firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\User firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\User firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\User updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, HasAuditName, HasDisplayName {
	/** @use HasFactory<\Database\Factories\UserFactory> */
	use Authenticatable, Authorizable, CausesActivity, HasFactory, HasUuids, LogsActivity, Notifiable, SoftDeletes;

	public $incrementing = false;
	protected $casts = [
		'role' => Role::class,
	];
	protected $fillable = [
		'badge_id',
		'username',
		'first_name',
		'last_name',
		'badge_name',
		'role',
	];
	protected $hidden = [
		'tg_setup_key',
		'tg_chat_id',
	];
	private ?bool $isGatekeeperCache = null;

	protected static function boot() {
		parent::boot();

		// Add a listener for the model being created to add a random tg_setup_key if one hasn't already been specified
		static::creating(function ($model) {
			if (!isset($model->tg_setup_key)) $model->generateTelegramSetupKey();
		});
	}

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['badge_id', 'username', 'first_name', 'last_name', 'badge_name', 'role', 'tg_chat_id'])
			->logOnlyDirty()
			->dontSubmitEmptyLogs();
	}

	public function getDisplayNameAttribute(): string {
		$name = $this->badge_name ?? $this->username;
		return !$this->deleted_at ? $name : "{$name} (del)";
	}

	public function getAuditNameAttribute(): string {
		return "{$this->display_name} (#{$this->badge_id})";
	}

	/**
	 * Get the full name of the user (first + last name)
	 */
	public function getFullNameAttribute(): string {
		return "{$this->first_name} {$this->last_name}";
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
	 * Get all attendee logs this user was entered into
	 */
	public function attendeeLogs(): BelongsToMany {
		return $this->belongsToMany(AttendeeLog::class)
			->withPivot('type')
			->withTimestamps()
			->withTrashed();
	}

	/**
	 * Scope a query to only include users of a given role
	 */
	public function scopeOfRole(Builder $query, Role $role): void {
		$query->where('role', $role->value);
	}

	/**
	 * Check whether the user should have access to a role's features
	 *
	 * @param bool [$strict=false] Whether to check specifically for the given role
	 */
	public function isRole(Role $role, bool $strict = false): bool {
		if ($strict) return $this->role->value === $role->value;
		return $this->role->value >= $role->value;
	}

	/**
	 * Check whether the user should have access to admin features
	 *
	 * @param bool [$strict=false] Whether to check specifically for the admin role
	 */
	public function isAdmin($strict = false): bool {
		return $this->isRole(Role::Admin, $strict);
	}

	/**
	 * Check whether the user should have access to manager features
	 *
	 * @param bool [$strict=false] Whether to check specifically for the manager role
	 */
	public function isManager($strict = false): bool {
		return $this->isRole(Role::Manager, $strict);
	}

	/**
	 * Check whether the user should have access to lead features
	 *
	 * @param bool [$strict=false] Whether to check specifically for the lead role
	 */
	public function isLead($strict = false): bool {
		return $this->isRole(Role::Lead, $strict);
	}

	/**
	 * Check whether the user should have access to staff features
	 *
	 * @param bool [$strict=false] Whether to check specifically for the staff role
	 */
	public function isStaff($strict = false): bool {
		return $this->isRole(Role::Staff, $strict);
	}

	/**
	 * Check whether the user has been banned
	 */
	public function isBanned(): bool {
		return $this->isRole(Role::Banned, true);
	}

	/**
	 * Check whether the user is a gatekeeper for any attendee logs that belong to the active event
	 */
	public function isGatekeeper(): bool {
		if ($this->isGatekeeperCache !== null) return $this->isGatekeeperCache;
		$this->isGatekeeperCache = $this->isManager()
			|| $this->attendeeLogs()->forEvent()->wherePivot('type', 'gatekeeper')->exists();
		return $this->isGatekeeperCache;
	}

	/**
	 * Check whether the user should see the functions panel (access to any possible backend functionality)
	 */
	public function hasAnyBackendAccess(): bool {
		return $this->isLead() || $this->isGatekeeper();
	}

	/**
	 * Gets information about the user's volunteer entities for an event
	 */
	public function getVolunteerInfo(?Event $event = null): array {
		$time = $this->getTimeStats($event);
		return [
			'user' => $this,
			'time' => $time,
			'reward_claims' => $this->rewardClaims,
		];
	}

	/**
	 * Get the total earned time (in seconds) for an event
	 *
	 * @param ?Event $event Event to get the earned time for - if null, then the active event will be used
	 * @param ?Collection $timeEntries Time entries to look through (to avoid extra queries if already retrieved)
	 */
	public function getEarnedTime(?Event $event = null, ?Collection $timeEntries = null): int {
		if (!$event) $event = Setting::activeEvent();

		// If there's no event at all, then skip all of the below work and just return empty results
		if (!$event) return 0;

		// Get all of the time entries from the user for the given event, along with the time bonuses that may apply
		if (!$timeEntries) {
			$timeEntries = $this->timeEntries()->with([
				'department.timeBonuses' => function ($query) use ($event) {
					$query->forEvent($event);
				},
			])->forEvent($event)->get();
		}
		$bonuses = $timeEntries->pluck('department.timeBonuses')->flatten()->unique('id');

		// Add up the duration and bonus time of all time entries to get the total time for the event
		return $timeEntries->reduce(
			fn (?int $carry, TimeEntry $entry) => $carry + $entry->calculateTotalTime($event, $bonuses),
			0
		);
	}

	/**
	 * Get statistics about the time spent
	 *
	 * @param ?Event $event Event to get the statistics for - if null, then the active event will be used
	 * @param ?Carbon $date Day for the day-specific statistics - if null, then today will be used
	 * @return array{total: int, bonus: float, day: int, entries: Collection<TimeEntry>, bonuses: Collection<TimeBonus>}
	 */
	public function getTimeStats(?Event $event = null, ?Carbon $date = null): array {
		if (!$event) $event = Setting::activeEvent();
		if (!$date) $date = now(config('tracker.timezone'));

		// If there's no event at all, then skip all of the below work and just return empty results
		if (!$event) {
			return [
				'total' => 0,
				'bonus' => 0,
				'day' => 0,
				'entries' => new Collection,
				'bonuses' => new Collection,
			];
		}

		// Get all of the time entries from the user for the given event, along with the time bonuses that may apply
		$timeEntries = $this->timeEntries()
			->with([
				'department.timeBonuses' => function ($query) use ($event) {
					$query->forEvent($event);
				},
			])
			->forEvent($event)
			->orderBy('start')
			->get();
		$bonuses = $timeEntries->pluck('department.timeBonuses')->flatten()->unique('id');

		// Add a bonus_time property to each entry and total them
		$bonusTime = 0;
		foreach ($timeEntries as $entry) {
			$entry->bonus_time = $entry->calculateBonusTime($event, $bonuses);
			$bonusTime += $entry->bonus_time;
		}

		// Add up the duration and bonus time of all time entries to get the total time for the event
		$totalTime = $bonusTime + $timeEntries->reduce(
			fn (?int $carry, TimeEntry $entry) => $carry + $entry->getDuration(),
			0,
		);

		// Get the date offset by the day boundary hour for day comparisons
		$boundaryHour = config('tracker.day_boundary_hour');
		$offsetDate = $date->avoidMutation()->subHours($boundaryHour);

		// Narrow down the time entries to ones that interact with the given date, then get the sum of them all
		// while taking into account only the time that crosses the day boundary if applicable
		$dayTime = $timeEntries->filter(
			fn (TimeEntry $entry) => $entry->getBoundaryOffsetStart(-1, $boundaryHour)->isSameDay($offsetDate) ||
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
			'bonus' => $bonusTime,
			'day' => $dayTime,
			'entries' => $timeEntries,
			'bonuses' => $bonuses,
		];
	}

	/**
	 * Get applicable rewards, claims, eligible/claimed rewards, and earned hours for an event
	 *
	 * @param ?Event $event Event to get the reward info for - if null, then the active event will be used
	 * @param ?float $earnedHours Number of earned hours (to avoid calculating it if it's already known)
	 * @param ?Collection $timeEntries Time entries to look through (to avoid extra queries if already retrieved)
	 * @return array{rewards: Collection<Reward>, eligible: Collection<Reward>, claimed: Collection<Reward>, claims: Collection<RewardClaim>, earnedHours: float}
	 */
	public function getRewardInfo(?Event $event = null, ?float $earnedHours = null, ?Collection $timeEntries = null): array {
		if (!$event) $event = Setting::activeEvent();

		// If there's no event at all, then skip all of the below work and just return empty results
		if (!$event) {
			return [
				'all' => new Collection,
				'eligible' => new Collection,
				'claimed' => new Collection,
				'claims' => new Collection,
			];
		}

		// Build the reward information
		if ($earnedHours === null) $earnedHours = $this->getEarnedTime($event, $timeEntries) / 60 / 60;
		return [
			'all' => $event->rewards,
			'eligible' => $event->rewards->filter(fn (Reward $reward) => $earnedHours > $reward->hours),
			'claimed' => $event->rewards->filter(fn (Reward $reward) => $this->rewardClaims->contains('reward_id', $reward->id)),
			'claims' => $this->rewardClaims->where('event_id', $event->id),
		];
	}

	/**
	 * Check whether the user has been notified of a reward being available
	 *
	 * @param ?Collection $notifications Notifications to look through (to avoid extra queries if already retrieved)
	 */
	public function hasBeenNotifiedForEligibleReward(Reward $reward, ?Collection $notifications = null): bool {
		if (!$notifications) $notifications = $this->notifications()->whereType(RewardAvailable::class)->get();
		return $notifications->contains('data.reward_id', $reward->id);
	}

	/**
	 * Get a URL to start interacting with the Telegram bot
	 */
	public function getTelegramSetupUrl(): string {
		$bot = Cache::flexible('telegram-bot', [60 * 10, 60 * 60], fn () => Telegram::getMe());
		return "https://t.me/{$bot->username}?start={$this->tg_setup_key}";
	}

	/**
	 * Generates and assigns a new Telegram setup key (tg_setup_key)
	 */
	public function generateTelegramSetupKey(): static {
		$this->tg_setup_key = Str::random(32);
		return $this;
	}

	/**
	 * Finds an existing user record and updates it with the latest information from an OAuth user, or creates a new
	 * record for it entirely.
	 */
	public static function updateOrCreateFromOAuthUser(OAuthUser $user): static {
		return static::updateOrCreate(['badge_id' => $user->id], [
			'username' => $user->nickname,
			'first_name' => $user->user['firstName'],
			'last_name' => $user->user['lastName'],
			'badge_name' => $user->user['badgeName'],
		]);
	}

	/**
	 * Creates a user from a ConCat registration object.
	 */
	public static function createFromConCatRegistration(object $registration, string $userType): static {
		return static::create([
			'username' => $registration->user->username,
			'first_name' => 'Unidentified',
			'last_name' => Str::title($userType),
			'badge_id' => $registration->user->id,
			'badge_name' => $registration->badgeName,
		]);
	}

	/**
	 * Retrieve volunteer and registration details for a badge ID and create a user with the information available from them.
	 * If all information fails to be retrieved, then a placeholder user with just the badge ID is created.
	 */
	public static function createWithAvailableDetails(int $badgeId, string $userType): static {
		$info = static::fetchAvailableDetails($badgeId);
		$volunteer = $info['volunteer'];
		$registration = $info['registration'];

		// Create the user with all available info
		return static::create([
			'username' => $volunteer?->user?->username ?? $registration?->user?->username ?? 'unknown',
			'first_name' => $volunteer?->user?->firstName ?? $registration?->user?->firstName ?? 'Unidentified',
			'last_name' => $volunteer?->user?->lastName ?? $registration?->user?->lastName ?? Str::title($userType),
			'badge_id' => $volunteer?->user?->id ?? $registration?->user?->id ?? $badgeId,
			'badge_name' => $registration?->badgeName,
		]);
	}

	/**
	 * Retrieves both the volunteer and registration information for a given user from ConCat and logs any failed requests
	 */
	private static function fetchAvailableDetails(int $badgeId): array {
		$volunteer = null;
		$registration = null;

		try {
			// First, authorize with ConCat - if this fails, then we needn't try anything else
			ConCat::authorize();

			// Try looking up a volunteer from the badge ID in ConCat so we get the volunteer user's
			// username and legal name
			try {
				$volunteer = ConCat::getVolunteer($badgeId);
			} catch (\Throwable $err) {
				Log::warning('Failed to look up ConCat volunteer for manual user creation', [
					'badge_id' => $badgeId,
					'error' => $err,
				]);
			}

			// Try looking up a registration from the badge ID in ConCat so we get the registration's badge name, plus
			// the registered user's username and legal name in case the volunteer lookup failed
			try {
				$registration = ConCat::getRegistration($badgeId);
			} catch (\Throwable $err) {
				Log::warning('Failed to look up ConCat registration for manual user creation', [
					'badge_id' => $badgeId,
					'error' => $err,
				]);
			}
		} catch (\Throwable $err) {
			Log::warning('Failed to authorize with ConCat for manual user creation', [
				'badge_id' => $badgeId,
				'error' => $err,
			]);
		}

		return [
			'volunteer' => $volunteer,
			'registration' => $registration,
		];
	}
}
