<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Event;
use App\Models\Reward;
use App\Models\Setting;
use App\Models\Activity;
use App\Models\TimeEntry;
use Illuminate\View\View;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;

class ManagementController extends Controller {
	/**
	 * Render the lead panel
	 */
	public function getLeadIndex(): View {
		return view('management.lead');
	}

	/**
	 * Render the management panel
	 */
	public function getManageIndex(): View {
		return view('management.manage', [
			'rewards' => Reward::forEvent()->orderBy('hours')->get(),
			'departments' => Department::orderBy('hidden')->orderBy('name')->get(),
			'longestOngoingEntries' => TimeEntry::with(['user', 'department'])
				->forEvent()
				->ongoing()
				->orderBy('start')
				->limit(10)
				->get(),
			'recentTimeActivities' => Activity::with(['subject', 'subject.user'])
				->whereHasMorph('subject', TimeEntry::class, function (Builder $query) {
					$query->whereEventId(Setting::activeEvent()?->id);
				})
				->orderByDesc('created_at')
				->limit(10)
				->get()
		]);
	}

	/**
	 * Render the site settings admin page
	 */
	public function getAdminSiteSettings(): View {
		return view('admin.site', [
			'activeEvent' => Setting::activeEvent(),
			'events' => Event::all(),
		]);
	}

	/**
	 * Render the user roles admin page
	 */
	public function getAdminUserRoles(): View {
		return view('admin.users', [
			'users' => User::where('role', '!=', Role::Volunteer)->get(),
			'roles' => Role::cases(),
		]);
	}

	/**
	 * Render the departments admin page
	 */
	public function getAdminDepartments(): View {
		return view('admin.departments', ['departments' => Department::all()]);
	}

	/**
	 * Render the events admin page
	 */
	public function getAdminEvents(): View {
		return view('admin.events', ['events' => Event::all()]);
	}

	/**
	 * Render the rewards admin page
	 */
	public function getAdminRewards(?Event $event = null): View|RedirectResponse {
		if (!$event) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('admin.event.rewards', $event);
		}

		return view('admin.rewards', [
			'activeEvent' => $event,
			'events' => Event::all(),
			'rewards' => $event?->rewards,
		]);
	}

	/**
	 * Render the bonuses admin page
	 */
	public function getAdminBonuses(?Event $event = null): View|RedirectResponse {
		if (!$event) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('admin.event.bonuses', $event);
		}

		return view('admin.bonuses', [
			'event' => $event,
			'events' => Event::all(),
			'departments' => Department::all()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE),
			'bonuses' => $event?->timeBonuses()?->with('departments')?->get(),
		]);
	}

	/**
	 * Render the reports admin page
	 */
	public function getAdminReports(?Event $event = null): View|RedirectResponse {
		if (!$event) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('admin.event.reports', $event);
		}

		return view('admin.reports', [
			'activeEvent' => $event,
			'events' => Event::all(),
		]);
	}
}
