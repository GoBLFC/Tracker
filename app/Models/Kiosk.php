<?php

namespace App\Models;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class Kiosk extends UuidModel {
	protected static function boot() {
		parent::boot();

		// Add a listener for the model being created to add a random session_key if one hasn't already been specified
		static::creating(function ($model) {
			if (!isset($model->session_key)) $model->session_key = Str::random(32);
		});
	}

	/**
	 * Get the Kiosk from the session, if there is one
	 */
	public static function findFromSession(): static {
		$sessionKey = Cookie::get('kiosk');
		if (!$sessionKey) return null;
		return static::findBySessionKey($sessionKey);
	}

	/**
	 * Check whether there is a valid Kiosk in the session
	 */
	public static function isSessionAuthorized(): bool {
		$sessionKey = Cookie::get('kiosk');
		if (!$sessionKey) return false;
		return static::whereSessionKey($sessionKey)->exists();
	}

	/**
	 * Create a new Kiosk and add it to the session
	 */
	public static function authorizeSession(): static {
		$kiosk = new static;
		$kiosk->save();
		Cookie::queue(Cookie::make('kiosk', $kiosk->session_key, 60 * 24 * 7));
		return $kiosk;
	}

	/**
	 * Remove the Kiosk that is in the session, if there is one
	 * @return ?boolean Whether a Kiosk was deleted from the database
	 */
	public static function deauthorizeSession(): ?bool {
		$kiosk = static::findFromSession();
		if ($kiosk === null) return false;
		Cookie::queue(Cookie::forget('kiosk'));
		return $kiosk->delete();
	}
}
