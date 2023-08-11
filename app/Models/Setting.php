<?php

namespace App\Models;

use App\Casts\JsonValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model {
	protected $keyType = 'string';

	protected $casts = [
		'value' => JsonValue::class,
	];

	/**
	 * Internal in-memory settings cache
	 *
	 * @var array<string, mixed>
	 */
	private static array $settingsCache = [];

	/**
	 * Stores a setting value in the database and clears any cached value for it
	 * @return int Number of affected rows (should always be 1 on success)
	 */
	public static function set(string $id, Model|array|bool|int|float|string|null $value): void {
		// Wipe the cache
		unset(static::$settingsCache[$id]);
		Cache::delete("setting:{$id}");

		// Update the value
		$count = static::whereId($id)->update([
			'value' => json_encode($value instanceof Model ? $value->getKey() : $value)
		]);

		// Make sure the update went through - if it didn't, it's for an unknown setting
		if ($count !== 1) throw new \ValueError("Unknown setting ID: {$id}");
	}

	/**
	 * Retrieves and caches whether the app should be in dev mode
	 */
	public static function isDevMode(): bool {
		return (bool) static::getAndCacheValue('dev-mode');
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
	 * @param string $id
	 * @param ?callable $transformer Function to mutate the setting value
	 */
	private static function getAndCacheValue(string $id, ?callable $transformer = null): mixed {
		// Return the setting value from the in-memory cache if it exists there
		if (array_key_exists($id, static::$settingsCache)) return static::$settingsCache[$id];

		// Retrieve the setting value from the cache provider if it exists there, otherwise obtain it from the DB and cache it
		$value = Cache::remember("setting:{$id}", 60 * 5, function () use ($id, $transformer) {
			$setting = static::findOrFail($id);
			if (!$setting || !$transformer) return $setting?->value;
			return $transformer($setting->value);
		});

		// Store the setting value in the in-memory cache
		static::$settingsCache[$id] = $value;
		return $value;
	}
}
