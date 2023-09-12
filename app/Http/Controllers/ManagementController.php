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
use App\Reports\AutoClosedTimeEntriesReport;
use Illuminate\Http\RedirectResponse;
use App\Reports\DepartmentSummaryReport;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManagementController extends Controller {
	public const REPORTS = [
		'departments' => DepartmentSummaryReport::class,
		'unclocked' => AutoClosedTimeEntriesReport::class,
	];

	public const REPORT_FILE_TYPES = [
		'xlsx' => 'Excel',
		'ods' => 'LibreOffice',
		'csv' => 'CSV',
		'pdf' => 'PDF',
		'html' => 'HTML',
	];

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
		// Get the event and redirect to the page for the active event, if applicable
		if (!$event) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('admin.event.rewards', $event);
		}

		return view('admin.rewards', [
			'event' => $event,
			'events' => Event::all(),
			'rewards' => $event?->rewards,
		]);
	}

	/**
	 * Render the bonuses admin page
	 */
	public function getAdminBonuses(?Event $event = null): View|RedirectResponse {
		// Get the event and redirect to the page for the active event, if applicable
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
	 * Render the reports list admin page
	 */
	public function getAdminReportList(?Event $event = null): View|RedirectResponse {
		// Get the event and redirect to the page for the active event, if applicable
		if (!$event) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('admin.event.reports', $event);
		}

		return view('admin.reports', [
			'event' => $event,
			'events' => Event::all(),
			'reports' => static::REPORTS,
		]);
	}

	/**
	 * Render a report
	 */
	public function getAdminReport(?Event $event = null, string $reportType) {
		// Get the event and redirect to the page for the active event, if applicable
		if (!$event || !$event->id) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('admin.event.reports.view', [$event, $reportType]);
		}

		// Get the report class to use
		$reportClass = static::REPORTS[$reportType] ?? null;
		if (!$reportClass) abort(404);

		return view('admin.report', [
			'report' => $event ? new $reportClass($event) : null,
			'reportType' => $reportType,
			'event' => $event,
			'events' => Event::all(),
			'reports' => static::REPORTS,
			'exportTypes' => static::REPORT_FILE_TYPES,
		]);
	}

	/**
	 * Provide an exported report file to download
	 */
	public function getAdminReportExport(?Event $event = null, string $reportType, string $fileType): BinaryFileResponse {
		// Get the event and redirect to the page for the active event, if applicable
		if (!$event || !$event->id) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('admin.event.reports', $event);
		}

		// Get the report class to use
		$reportClass = static::REPORTS[$reportType] ?? null;
		if (!$reportClass) abort(404);

		// Validate the file type and export the report
		if (!array_key_exists($fileType, static::REPORT_FILE_TYPES)) abort(404);
		$report = new $reportClass($event);
		return $report->download($report->filename($fileType));
	}
}
