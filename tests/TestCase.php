<?php

namespace Tests;

use App\Models\Kiosk;
use App\Models\Setting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected function setUp(): void {
		parent::setUp();
		static::resetStaticCaches();
	}

	/**
	 * Clears the settings and kiosk authorization caches that models keep in static properties.
	 * They would otherwise carry over between tests and between requests made within a single test.
	 */
	public static function resetStaticCaches(): void {
		(fn () => static::$settingsCache = [])->bindTo(null, Setting::class)();
		(fn () => static::$authorizedCache = null)->bindTo(null, Kiosk::class)();
	}
}
