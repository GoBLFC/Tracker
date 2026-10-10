<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Setting;
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
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller {
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
	 * Render the reports list admin page
	 */
	public function index(?Event $event = null): Response|RedirectResponse {
		// Get the event and redirect to the page for the active event, if applicable
		if (!$event) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('events.reports.index', $event);
		}

		return Inertia::render('Reports', [
			'reports' => fn () => static::getReportTypes(),
			'event' => fn () => $event,
			'events' => fn () => Event::orderBy('name')->get(),
		]);
	}

	/**
	 * Render a report
	 */
	public function show(?Event $event, string $reportType): Response|RedirectResponse {
		// Get the event and redirect to the page for the active event, if applicable
		if (!$event || !$event->id) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('events.reports.show', [$event, $reportType]);
		}

		// Get the report class to use and create the report
		$reportClass = static::REPORTS[$reportType] ?? null;
		if (!$reportClass) abort(404);

		return Inertia::render('Reports', [
			'report' => Inertia::defer(function () use ($event, $reportClass) {
				$report = $this->createReport($event, $reportClass);
				return [
					'data' => $report->toCollection(),
					'meta' => [
						'formats' => static::getColumnFormats($report),
						'defaultSort' => [
							'column' => $report->defaultSortColumn(),
							'direction' => $report->defaultSortDirection(),
						],
					],
				];
			}),
			'reports' => fn () => static::getReportTypes(),
			'exportTypes' => fn () => Report::EXPORT_FILE_TYPES,
			'event' => fn () => $event,
			'events' => fn () => Event::orderBy('name')->get(),
		]);
	}

	/**
	 * Provide an exported report file to download
	 */
	public function export(?Event $event, string $reportType, string $fileType): BinaryFileResponse|RedirectResponse {
		// Get the event and redirect to the page for the active event, if applicable
		if (!$event || !$event->id) {
			$event = Setting::activeEvent();
			if ($event) return redirect()->route('events.reports.index', $event);
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
	private function createReport(?Event $event, string $reportClass): ?Report {
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

	/**
	 * Gets the available report types
	 *
	 * @return array{name: string, slug: string, eventOnly: bool, standardColumns: string[]}[]
	 */
	private static function getReportTypes(): array {
		$types = [];
		foreach (static::REPORTS as $slug => $class) {
			if ($class::hide()) continue;

			$type = [
				'name' => $class::name(),
				'slug' => $slug,
				'eventOnly' => is_subclass_of($class, EventReport::class),
				'standardColumns' => $class::standardColumns(),
			];

			if (is_subclass_of($class, WithExtraParam::class)) {
				$type['extraParam'] = [
					'key' => $class::extraParamKey(),
					'default' => $class::extraParamDefaultValue(),
					'choices' => $class::extraParamChoices(),
				];
			}

			$types[] = $type;
		}

		return $types;
	}

	/**
	 * Gets the column formats for a report
	 *
	 * @return array{type: 'string' | 'number' | 'date', decimal: bool, separators: bool}[]
	 */
	private static function getColumnFormats(Report $report): ?array {
		if (!$report instanceof WithColumnFormatting) return null;

		$colCount = \count($report->headings());
		$rawFormats = $report->columnFormats();

		$formats = [];
		$letterOffset = \ord('A');
		for ($c = 0; $c < $colCount; $c++) {
			$letter = \chr($letterOffset + $c);
			$formats[] = isset($rawFormats[$letter]) ? static::getColumnFormat($rawFormats[$letter]) : ['type' => 'string'];
		}

		return $formats;
	}

	/**
	 * Gets formatting information for a raw PhpSpreadsheet format
	 *
	 * @return array{type: 'string' | 'number' | 'date', decimal: bool, separators: bool}
	 */
	private static function getColumnFormat(string $format): array {
		switch ($format) {
			case NumberFormat::FORMAT_NUMBER:
				return ['type' => 'number', 'decimal' => false, 'separators' => false];
			case NumberFormat::FORMAT_NUMBER_0:
			case NumberFormat::FORMAT_NUMBER_00:
				return ['type' => 'number', 'decimal' => true, 'separators' => false];
			case NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1:
			case NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2:
				return ['type' => 'number', 'decimal' => true, 'separators' => true];
			case NumberFormat::FORMAT_DATE_DATETIME:
				return ['type' => 'date'];
		}

		throw new \InvalidArgumentException("Unknown column format: {$format}");
	}
}
