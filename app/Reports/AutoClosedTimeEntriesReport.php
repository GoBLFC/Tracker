<?php

namespace App\Reports;

use App\Models\TimeEntry;
use App\Reports\Concerns\FormatsAsTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AutoClosedTimeEntriesReport extends EventReport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStrictNullComparison {
	use FormatsAsTable, RegistersEventListeners;

	public function query(): Builder {
		return TimeEntry::with('user', 'department')
			->forEvent($this->event)
			->whereAuto(true)
			->orderBy('updated_at', 'desc');
	}

	/** @param TimeEntry $entry */
	public function map($entry, $excelDates = true): array {
		$start = $entry->start->timezone(config('tracker.timezone'));
		$stop = $entry->stop->timezone(config('tracker.timezone'));
		return [
			$entry->user->badge_id,
			$entry->user->username,
			$excelDates ? Date::dateTimeToExcel($start) : $start,
			$excelDates ? Date::dateTimeToExcel($stop) : $stop,
			$entry->department->display_name,
		];
	}

	public function headings(): array {
		return [
			'Badge Number',
			'Username',
			'Check-in',
			'Auto check-out',
			'Department',
		];
	}

	public function columnFormats(): array {
		return [
			'A' => NumberFormat::FORMAT_NUMBER,
			'C' => NumberFormat::FORMAT_DATE_DATETIME,
			'D' => NumberFormat::FORMAT_DATE_DATETIME,
		];
	}

	public static function name(): string {
		return 'Unclocked Users';
	}

	public static function slug(): string {
		return 'unclocked';
	}

	public static function defaultSortColumn(): int {
		return 3;
	}

	public static function defaultSortDirection(): string {
		return 'desc';
	}

	public static function afterSheet(AfterSheet $event) {
		static::formatAsTable($event->sheet);
	}
}
