<?php

namespace App\Reports;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Collection;
use App\Reports\Concerns\WithTotals;
use App\Reports\Concerns\FormatsAsTable;
use App\Reports\Concerns\WithExtraParam;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class VolunteerTimeReport extends EventReport implements FromCollection, WithMapping, WithHeadings, WithColumnFormatting, WithExtraParam, WithTotals, WithStrictNullComparison, WithEvents, ShouldAutoSize {
	use RegistersEventListeners, FormatsAsTable;

	public function __construct(Event $event, public int $extraParam) {
		parent::__construct($event);
	}

	public function collection(): Collection {
		return User::orderBy('badge_id')
			->with([
				'timeEntries' => function ($query) {
					$query->forEvent($this->event);
				},
				'timeEntries.department',
				'timeEntries.department.timeBonuses' => function ($query) {
					$query->forEvent($this->event);
				},
			])
			->whereHas('timeEntries', function ($query) {
				$query->forEvent($this->event);
			})
			->get()
			->filter(fn ($user) => $user->getEarnedTime($this->event, $user->timeEntries) / 60 / 60 > $this->extraParam);
	}

	/** @var User $user */
	public function map($user, $excelDates = true): array {
		return [
			$user->badge_id,
			$user->username,
			$user->timeEntries->map(fn ($entry) => $entry->getDuration() / 60 / 60)->sum(),
			$user->getEarnedTime($this->event, $user->timeEntries) / 60 / 60,
			$user->timeEntries->pluck('department.name')->unique()->sort()->join(', '),
		];
	}

	public function headings(): array {
		return [
			'Badge Number',
			'Username',
			'Hours Worked',
			'Hours Earned',
			'Departments',
		];
	}

	public function columnFormats(): array {
		return [
			'A' => NumberFormat::FORMAT_NUMBER,
			'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
			'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
		];
	}

	public function totalsLabel(): string {
		return 'Totals';
	}

	public function totalsFunctions(): array {
		return [
			'none',
			'none',
			'sum',
			'sum',
			'none',
		];
	}

	public static function name(): string {
		return 'Volunteer Hours';
	}

	public static function slug(): string {
		return 'hours';
	}

	public static function defaultSortColumn(): int {
		return 3;
	}

	public static function defaultSortDirection(): string {
		return 'desc';
	}

	public static function extraParamKey(): string {
		return 'threshold';
	}

	public static function extraParamDefaultValue(): int {
		return 0;
	}

	public static function extraParamChoices(): array {
		return [
			1 => '> 1 Hour',
			4 => '> 4 Hours',
			8 => '> 8 Hours',
			12 => '> 12 Hours',
			24 => '> 24 Hours',
			0 => 'All',
		];
	}

	public static function afterSheet(AfterSheet $event) {
		static::formatAsTable($event->sheet);
	}
}
