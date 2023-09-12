<?php

namespace App\Reports;

use App\Models\TimeEntry;
use App\Models\Department;
use App\Models\User;
use App\Reports\Concerns\FormatsAsTable;
use App\Reports\Concerns\WithTotals;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class DepartmentSummaryReport extends EventReport implements FromQuery, WithMapping, WithHeadings, WithColumnFormatting, WithTotals, WithStrictNullComparison, WithEvents, ShouldAutoSize {
	use RegistersEventListeners, FormatsAsTable;

	public function query(): Builder {
		return Department::with([
			'timeEntries' => function ($query) {
				$query->forEvent($this->event);
			},
		])->orderBy('name');
	}

	/** @var Department $department */
	public function map($department): array {
		return [
			$department->display_name,
			$department->timeEntries->sum(fn (TimeEntry $entry) => $entry->getDuration() / 60 / 60),
			$department->timeEntries->unique('user_id')->count(),
			$department->timeEntries->count(),
		];
	}

	public function headings(): array {
		return [
			'Department',
			'Hours',
			'Volunteers',
			'Time Entries',
		];
	}

	public function columnFormats(): array {
		return [
			'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
			'C' => NumberFormat::FORMAT_NUMBER,
			'D' => NumberFormat::FORMAT_NUMBER,
		];
	}

	public function totalsLabel(): string {
		return 'Totals';
	}

	public function totalsFunctions(): array {
		return [
			'none',
			'sum',
			User::whereHas('timeEntries', function ($query) {
				$query->forEvent($this->event);
			})->count(),
			'sum',
			'none',
		];
	}

	public static function name(): string {
		return 'Department Summary';
	}

	public static function slug(): string {
		return 'departments';
	}

	public static function afterSheet(AfterSheet $event) {
		static::formatAsTable($event->sheet);
	}
}
