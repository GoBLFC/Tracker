<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Event;
use App\Models\Reward;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class ManagementController extends Controller {
	/**
	 * Render the management panel
	 */
	public function getManageIndex(?Event $event = null, ?User $user = null): Response {
		if (!$event) $event = Setting::activeEvent();
		if ($event) $this->authorize('view', $event);

		return Inertia::render('ManagerDashboard', [
			'event' => $event,
			'events' => fn () => Event::orderBy('name')->get(),
			'rewards' => fn () => Reward::forEvent($event)->orderBy('hours')->get(),
			'departments' => fn () => $event
				? $event->departments()->orderBy('hidden')->orderBy('name')->get()
				: null,
			'ongoingEntries' => Inertia::defer(
				fn () => TimeEntry::with(['user', 'department'])
					->forEvent($event)
					->ongoing()
					->orderBy('start')
					->get()
			),
			'recentTimeActivities' => Inertia::defer(
				fn () => Activity::with(['subject', 'subject.user'])
					->whereHasMorph('subject', TimeEntry::class, function (Builder $query) use ($event) {
						$query->whereEventId($event?->id);
					})
					->orderByDesc('created_at')
					->limit(20)
					->get()
			),
			'volunteer' => fn () => $user?->getVolunteerInfo($event),
		]);
	}
}
