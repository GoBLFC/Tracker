<?php

namespace App\Reports;

use App\Models\User;
use App\Models\Event;
use App\Models\AttendeeLog;
use Illuminate\Support\Str;
use App\Reports\Concerns\FormatsAsTable;
use App\Reports\Concerns\WithExtraParam;
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

class AttendeeLogReport extends EventReport implements FromQuery, WithMapping, WithHeadings, WithColumnFormatting, WithExtraParam, WithStrictNullComparison, WithEvents, ShouldAutoSize {
	use RegistersEventListeners, FormatsAsTable;

	public AttendeeLog $attendeeLog;

	public function __construct(Event $event, string $attendeeLogId) {
		parent::__construct($event);
		$this->attendeeLog = AttendeeLog::findOrFail($attendeeLogId);
	}

	public function query(): Builder {
		return $this->attendeeLog->attendees()->withPivot('created_at');
	}

	/** @var User $user */
	public function map($user, $excelDates = true): array {
		$arrival = $user->pivot->created_at->timezone(config('tracker.timezone'));

		return [
			$user->badge_id,
			$user->display_name,
			$excelDates ? Date::dateTimeToExcel($arrival) : $arrival,
		];
	}

	public function headings(): array {
		return [
			'Badge Number',
			'Name',
			'Arrival',
		];
	}

	public function columnFormats(): array {
		return [
			'A' => NumberFormat::FORMAT_NUMBER,
			'C' => NumberFormat::FORMAT_DATE_DATETIME,
		];
	}

	public function filename(string $extension): string {
		$fileName = parent::filename($extension);
		$logSlug = Str::slug($this->attendeeLog->display_name);
		return "{$logSlug}-{$fileName}";
	}

	public function properties(): array {
		$lcName = Str::lower(static::name());
		return array_merge([
			'title' => "Attendee Log - {$this->attendeeLog->display_name}",
			'description' => "{$this->attendeeLog->display_name} {$lcName}",
		], parent::properties());
	}

	public static function name(): string {
		return 'Attendee Log';
	}

	public static function slug(): string {
		return 'attendee-log';
	}

	public static function hide(): bool {
		return true;
	}

	public static function defaultSortColumn(): int {
		return 2;
	}

	public static function defaultSortDirection(): string {
		return 'desc';
	}

	public static function extraParamKey(): string {
		return 'id';
	}

	public static function extraParamDefaultValue(): int {
		return 0;
	}

	public static function extraParamChoices(): array {
		return [];
	}

	public static function afterSheet(AfterSheet $event) {
		static::formatAsTable($event->sheet);
	}
}
