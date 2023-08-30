<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;

class UserEventSubscriber {
	public function __construct(
		public readonly Logger $log,
		public readonly Request $request,
	) {
		// nothing to do
	}

	/**
	 * Handle user login events.
	 */
	public function handleUserLogin(Login $event): void {
		$this->log->info('User logged in', [
			'user' => $event->user->id,
			'ip' => $this->request->ip(),
			'userAgent' => $this->request->userAgent()
		]);
	}

	/**
	 * Handle user logout events.
	 */
	public function handleUserLogout(Logout $event): void {
		$this->log->info('User logged out', [
			'user' => $event->user->id,
			'ip' => $this->request->ip(),
			'userAgent' => $this->request->userAgent()
		]);
	}

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @return array<string, string>
	 */
	public function subscribe(Dispatcher $events): array {
		return [
			Login::class => 'handleUserLogin',
			Logout::class => 'handleUserLogout',
		];
	}
}
