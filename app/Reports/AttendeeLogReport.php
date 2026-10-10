<?php

namespace App\Reports;

use App\Models\AttendeeLog;
use App\Models\Event;
use App\Models\User;
use App\Reports\Concerns\FormatsAsTable;
use App\Reports\Concerns\WithExtraParam;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Str;
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

class AttendeeLogReport extends EventReport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithEvents, WithExtraParam, WithHeadings, WithMapping, WithStrictNullComparison {
	use FormatsAsTable, RegistersEventListeners;

	public AttendeeLog $attendeeLog;

	/**
	 * Display names of users that overrode entry requirements for attendees in the log, keyed by user ID
	 *
	 * @var array<string, string>|null
	 */
	protected ?array $overriderNames = null;

	public function __construct(Event $event, string $attendeeLogId) {
		parent::__construct($event);
		$this->attendeeLog = AttendeeLog::findOrFail($attendeeLogId);
	}

	public function query(): Builder {
		return $this->attendeeLog->attendees()->withPivot('created_at');
	}

	/** @param User $user */
	public function map($user, $excelDates = true): array {
		$arrival = $user->pivot->created_at->timezone(config('tracker.timezone'));

		return [
			$user->badge_id,
			static::escapeFormula($user->display_name),
			$excelDates ? Date::dateTimeToExcel($arrival) : $arrival,
			static::escapeFormula($this->getOverriderName($user->pivot->overridden_by_id)),
			static::escapeFormula($user->pivot->override_reason),
		];
	}

	public function headings(): array {
		return [
			'Badge Number',
			'Name',
			'Arrival',
			'Overridden By',
			'Override Reason',
		];
	}

	/**
	 * Gets the display name of a user that overrode entry requirements for an attendee
	 */
	protected function getOverriderName(?string $userId): ?string {
		if (!$userId) return null;
		$this->overriderNames ??= User::withTrashed()->whereIn(
			'id',
			$this->attendeeLog->attendees()->wherePivotNotNull('overridden_by_id')->pluck('overridden_by_id'),
		)->get()->mapWithKeys(fn (User $overrider) => [$overrider->id => $overrider->display_name])->all();
		return $this->overriderNames[$userId] ?? null;
	}

	/**
	 * Prefixes text that a spreadsheet would treat as a formula (starting with =, +, -, @, a tab, or a carriage
	 * return) with an apostrophe, so that names and reasons entered by users are always shown as plain text. This
	 * applies to every export format, including CSV.
	 */
	protected static function escapeFormula(?string $value): ?string {
		if ($value === null || !preg_match('/^[=+\-@\t\r]/', $value)) return $value;
		return "'{$value}";
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
