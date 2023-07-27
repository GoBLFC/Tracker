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
	 * Retrieves and caches whether the app should be in dev mode
	 */
	public static function isDevMode(): bool {
		return (bool) static::getAndCacheValue('dev-mode');
	}

	/**
	 * Retrieves and caches the active event
	 */
	public static function activeEvent(): ?Event {
		return static::getAndCacheValue('active-event', fn ($val) => Event::find($val));
	}

	/**
	 * Retrieve the value of a setting from the in-memory cache, cache provider, or database, and cache it appropriately
	 *
	 * @param string $id
	 * @param ?callable $transformer Function to mutate the setting value
	 */
	private static function getAndCacheValue(string $id, ?callable $transformer = null): mixed {
		// Return the setting value from the in-memory cache if it exists there
		if (isset(static::$settingsCache[$id])) return static::$settingsCache[$id];

		// Retrieve the setting value from the cache provider if it exists there, otherwise obtain it from the DB and cache it
		$value = Cache::remember("setting:{$id}", 60 * 5, function () use ($id, $transformer) {
			$setting = static::find($id);
			if (!$setting || !$transformer) return $setting?->value;
			return $transformer($setting->value);
		});

		// Store the setting value in the in-memory cache
		static::$settingsCache[$id] = $value;
		return $value;
	}
}
