<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\MassPrunable;

/**
 * @property string $session_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Activity>|\App\Models\Activity[] $activities
 * @property-read int|null $activities_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Kiosk unexpired()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Kiosk expired()
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\Kiosk firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\Kiosk firstOrFail($columns = ['*'])
 * @method \App\Models\Kiosk firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\Kiosk firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\Kiosk firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\Kiosk updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class Kiosk extends UuidModel {
	use MassPrunable, LogsActivity;

	/**
	 * Cached authorization status
	 */
	private static ?bool $authorizedCache = null;

	protected static function boot() {
		parent::boot();

		// Add a listener for the model being created to add a random session_key if one hasn't already been specified
		static::creating(function ($model) {
			if (!isset($model->session_key)) $model->session_key = Str::random(32);
		});
	}

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->submitEmptyLogs();
	}

	/**
	 * Get the prunable model query.
	 */
	public function prunable(): Builder {
		return static::expired();
	}

	/**
	 * Scope a query to only include unexpired kiosks
	 */
	public function scopeUnexpired(Builder $query): void {
		$query->where('updated_at', '>', now()->subMinutes(config('tracker.kiosk_lifetime')));
	}

	/**
	 * Scope a query to only include expired kiosks
	 */
	public function scopeExpired(Builder $query): void {
		$query->where('updated_at', '<=', now()->subMinutes(config('tracker.kiosk_lifetime')));
	}

	/**
	 * Sets a cookie that contains the session_key of this kiosk
	 */
	public function authorize(): void {
		static::$authorizedCache = true;
		Cookie::queue(Cookie::make('kiosk', $this->session_key, config('tracker.kiosk_lifetime')));
	}

	/**
	 * Deletes the Kiosk from the database and deletes the kiosk cookie for the current session if it matches
	 * @return ?bool Whether the Kiosk was deleted from the database
	 */
	public function deauthorize(): ?bool {
		if (Cookie::get('kiosk') === $this->session_key) {
			static::$authorizedCache = false;
			Cookie::queue(Cookie::forget('kiosk'));
		}

		return $this->delete();
	}

	/**
	 * Get the Kiosk from the session, if there is one
	 */
	public static function findFromSession(): ?static {
		$sessionKey = Cookie::get('kiosk');
		if (!$sessionKey) return null;
		return static::unexpired()->whereSessionKey($sessionKey)->firstOrFail();
	}

	/**
	 * Check whether the current session is authorized as a kiosk
	 * @param bool $strict If false, dev mode will always allow authorization (default false)
	 */
	public static function isSessionAuthorized(bool $strict = false): bool {
		if (static::$authorizedCache !== null) return static::$authorizedCache || (!$strict && Setting::isDevMode());

		$sessionKey = Cookie::get('kiosk');
		if (!$sessionKey) return !$strict && Setting::isDevMode();

		static::$authorizedCache = static::unexpired()->whereSessionKey($sessionKey)->exists();
		return static::$authorizedCache || (!$strict && Setting::isDevMode());
	}

	/**
	 * Create a new Kiosk and add it to the session
	 */
	public static function authorizeSession(): static {
		$kiosk = new static;
		$kiosk->save();
		$kiosk->authorize();
		return $kiosk;
	}

	/**
	 * Remove the Kiosk that is in the session, if there is one
	 * @return ?bool Whether a Kiosk was deleted from the database
	 */
	public static function deauthorizeSession(): ?bool {
		$kiosk = static::findFromSession();
		if (!$kiosk) return false;
		return $kiosk->deauthorize();
	}
}
