<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Department;
use App\Models\Event;
use App\Models\Reward;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Models\User;
use App\Reports\AttendeeLogReport;
use App\Reports\AuditLogReport;
use App\Reports\AutoClosedTimeEntriesReport;
use App\Reports\Concerns\WithExtraParam;
use App\Reports\DepartmentSummaryReport;
use App\Reports\EventReport;
use App\Reports\Report;
use App\Reports\VolunteerApplicationDepartmentSummaryReport;
use App\Reports\VolunteerApplicationsReport;
use App\Reports\VolunteerTimeReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManagementController extends Controller {
	public const REPORTS = [
		'hours' => VolunteerTimeReport::class,
		'departments' => DepartmentSummaryReport::class,
		'unclocked' => AutoClosedTimeEntriesReport::class,
		'applications' => VolunteerApplicationsReport::class,
		'application-departments' => VolunteerApplicationDepartmentSummaryReport::class,
		'audit' => AuditLogReport::class,
		'attendee-log' => AttendeeLogReport::class,
	];

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
			'departments' => fn () => Department::orderBy('hidden')->orderBy('name')->get(),
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
	public function getAdminReport(?Event $event, string $reportType) {
		// Get the event and redirect to the page for the active event, if applicable
		if (!$event || !$event->id) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('admin.event.reports.view', [$event, $reportType]);
		}

		// Get the report class to use and create the report
		$reportClass = static::REPORTS[$reportType] ?? null;
		if (!$reportClass) abort(404);
		$report = $this->createReport($event, $reportClass);

		return view('admin.report', [
			'report' => $report,
			'reportType' => $reportType,
			'event' => $event,
			'events' => Event::all(),
			'reports' => static::REPORTS,
			'exportTypes' => Report::EXPORT_FILE_TYPES,
		]);
	}

	/**
	 * Provide an exported report file to download
	 */
	public function getAdminReportExport(?Event $event, string $reportType, string $fileType): BinaryFileResponse|RedirectResponse {
		// Get the event and redirect to the page for the active event, if applicable
		if (!$event || !$event->id) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('admin.event.reports', $event);
		}

		// Get the report class to use
		$reportClass = static::REPORTS[$reportType] ?? null;
		if (!$reportClass) abort(404);

		// Validate the file type
		if (!isset(Report::EXPORT_FILE_TYPES[$fileType])) abort(404);

		$report = $this->createReport($event, $reportClass);
		return $report->download($report->filename($fileType));
	}

	/**
	 * Instantiate a report and prefetch data for it
	 */
	private function createReport(Event $event, string $reportClass): ?Report {
		$report = null;
		$args = [];

		if (is_subclass_of($reportClass, EventReport::class)) {
			if (!$event) return null;
			$args[] = $event;
		}

		if (is_subclass_of($reportClass, WithExtraParam::class)) {
			$args[] = request()->input(
				$reportClass::extraParamKey(),
				$reportClass::extraParamDefaultValue(),
			);
		}

		$report = new $reportClass(...$args);
		$report->prefetch();
		return $report;
	}
}
