<?php

namespace App\Models;

use App\Casts\JsonValue;
use App\Models\Contracts\HasDisplayName;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property string $name
 * @property mixed $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Activity>|\App\Models\Activity[] $activities
 * @property-read int|null $activities_count
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\Setting firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\Setting firstOrFail($columns = ['*'])
 * @method \App\Models\Setting firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\Setting firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\Setting firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\Setting updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class Setting extends UuidModel implements HasDisplayName {
	use LogsActivity;

	protected $casts = [
		'value' => JsonValue::class,
	];

	/**
	 * Internal in-memory settings cache
	 *
	 * @var array<string, mixed>
	 */
	private static array $settingsCache = [];

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['value'])
			->logOnlyDirty()
			->submitEmptyLogs();
	}

	public function getDisplayNameAttribute(): string {
		return $this->name;
	}

	/**
	 * Stores a new value for the setting in the database and clears any cached value for it
	 */
	public function setValue(Model|array|bool|int|float|string|null $value): void {
		// Wipe the cache
		unset(static::$settingsCache[$this->name]);
		Cache::delete("setting:{$this->name}");

		// Update the value
		$this->value = $value instanceof Model ? $value->getKey() : $value;
		$this->save();
	}

	/**
	 * Stores a setting value in the database and clears any cached value for it
	 */
	public static function set(string $name, Model|array|bool|int|float|string|null $value): void {
		// Wipe the cache
		unset(static::$settingsCache[$name]);
		Cache::delete("setting:{$name}");

		// Update the value
		$count = static::whereName($name)->update([
			'value' => json_encode($value instanceof Model ? $value->getKey() : $value)
		]);

		// Make sure the update went through - if it didn't, it's for an unknown setting
		if ($count !== 1) throw new \ValueError("Unknown setting name: {$name}");
	}

	/**
	 * Retrieves and caches whether the app should be in dev mode
	 */
	public static function isDevMode(): bool {
		return (bool) static::getAndCacheValue('dev-mode');
	}

	/**
	 * Retrieves and caches whether the app should be locked down
	 */
	public static function isLockedDown(): bool {
		return (bool) static::getAndCacheValue('lockdown');
	}

	/**
	 * Retrieves and caches the active event
	 */
	public static function activeEvent(): ?Event {
		return static::getAndCacheValue('active-event', fn (?string $val) => Event::find($val));
	}

	/**
	 * Retrieve the value of a setting from the in-memory cache, cache provider, or database, and cache it appropriately
	 *
	 * @param string $name
	 * @param ?callable $transformer Function to mutate the setting value
	 */
	private static function getAndCacheValue(string $name, ?callable $transformer = null): mixed {
		// Return the setting value from the in-memory cache if it exists there
		if (array_key_exists($name, static::$settingsCache)) return static::$settingsCache[$name];

		// Retrieve the setting value from the cache provider if it exists there, otherwise obtain it from the DB and cache it
		$value = Cache::remember("setting:{$name}", 60 * 5, function () use ($name, $transformer) {
			$setting = static::whereName($name)->firstOrFail();
			if (!$setting || !$transformer) return $setting?->value;
			return $transformer($setting->value);
		});

		// Store the setting value in the in-memory cache
		static::$settingsCache[$name] = $value;
		return $value;
	}
}
