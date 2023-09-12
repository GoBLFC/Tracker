<?php

namespace App\Reports;

use App\Models\TimeEntry;
use App\Reports\Concerns\FormatsAsTable;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class AutoClosedTimeEntriesReport extends EventReport implements FromQuery, WithMapping, WithHeadings, WithColumnFormatting, WithStrictNullComparison, WithEvents, ShouldAutoSize {
	use RegistersEventListeners, FormatsAsTable;

	public function query(): Builder {
		return TimeEntry::with('user', 'department')
			->forEvent($this->event)
			->whereAuto(true)
			->orderBy('updated_at');
	}

	/** @var TimeEntry $entry */
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

	public static function afterSheet(AfterSheet $event) {
		static::formatAsTable($event->sheet);
	}
}
