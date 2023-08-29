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
use Illuminate\Contracts\Database\Eloquent\Builder;

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
		return view('admin.users', ['users' => User::where('role', '!=', Role::Volunteer)->get()]);
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
		return view('admin.rewards', ['events' => Event::all()]);
	}

	/**
	 * Render the rewards admin page
	 */
	public function getAdminRewards(?Event $event = null): View {
		if (!$event) $event = Setting::activeEvent();
		return view('admin.rewards', [
			'activeEvent' => $event,
			'events' => Event::all(),
			'rewards' => $event?->rewards,
		]);
	}

	/**
	 * Render the bonuses admin page
	 */
	public function getAdminBonuses(?Event $event = null): View {
		if (!$event) $event = Setting::activeEvent();
		return view('admin.bonuses', [
			'activeEvent' => $event,
			'events' => Event::all(),
			'bonuses' => $event?->timeBonuses,
		]);
	}

	/**
	 * Render the reports admin page
	 */
	public function getAdminReports(?Event $event = null): View {
		return view('admin.reports', [
			'activeEvent' => $event,
			'events' => Event::all(),
		]);
	}
}
