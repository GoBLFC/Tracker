<?php

namespace App\Listeners;


use Illuminate\Log\Logger;
use Illuminate\Http\Request;
use App\Events\QuickCodeLogin;
use App\Events\QuickCodeGenerated;
use Illuminate\Auth\Events\Login;
use Illuminate\Events\Dispatcher;
use Illuminate\Auth\Events\Logout;

class AuthEventSubscriber {
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
			'userAgent' => $this->request->userAgent(),
		]);
	}

	/**
	 * Handle user logout events.
	 */
	public function handleUserLogout(Logout $event): void {
		$this->log->info('User logged out', [
			'user' => $event->user->id,
			'ip' => $this->request->ip(),
			'userAgent' => $this->request->userAgent(),
		]);
	}

	/**
	 * Handle quick code login events.
	 */
	public function handleQuickCodeLogin(QuickCodeLogin $event): void {
		$this->log->info('Quick code used', [
			'user' => $event->quickCode->user->id,
			'quickCode' => $event->quickCode->id,
		]);
	}

	/**
	 * Handle quick code creation events.
	 */
	public function handleQuickCodeGenerated(QuickCodeGenerated $event): void {
		$this->log->info('Quick code generated', [
			'user' => $event->quickCode->user->id,
			'quickCode' => $event->quickCode->id,
			'tgChat' => $event->quickCode->user->tg_chat_id,
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
			QuickCodeLogin::class => 'handleQuickCodeLogin',
			QuickCodeGenerated::class => 'handleQuickCodeGenerated',
		];
	}
}
