<?php

namespace App\Models;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use SocialiteProviders\Manager\OAuth2\User as OauthUser;

class User extends Authenticatable {
	use HasFactory, Notifiable;

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
		'tg_setup_code',
	];

	protected static function boot() {
		parent::boot();

		// Add a listener for the model being created to add a random tg_setup_code if one hasn't already been specified
		static::creating(function ($model) {
			if (!isset($model->tg_setup_code)) $model->tg_setup_code = Str::random(32);
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
	 * Get all notifications for the user
	 */
	public function notifications(): HasMany {
		return $this->hasMany(Notification::class);
	}

	/**
	 * Get unread notifications for the user
	 */
	public function unreadNotifications(): QueryBuilder {
		return $this->notifications()->whereHasRead(false);
	}

	/**
	 * Get all the departments the user has entered time for
	 */
	public function departments(): HasManyThrough {
		return $this->hasManyThrough(Department::class, TimeEntry::class);
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
