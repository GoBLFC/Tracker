<?php

namespace App\Reports;

use App\Models\Reward;
use App\Models\Activity;
use App\Models\TimeBonus;
use App\Models\TimeEntry;
use App\Models\RewardClaim;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Reports\Concerns\FormatsAsTable;
use App\Reports\Concerns\WithExtraParam;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class AuditLogReport extends Report implements FromQuery, WithMapping, WithHeadings, WithColumnFormatting, WithExtraParam, WithStrictNullComparison, WithEvents, ShouldAutoSize {
	use RegistersEventListeners, FormatsAsTable;

	public function __construct(public int $days) {
		// nothing to do
	}

	public function query(): Builder {
		$query = Activity::query()
			->withoutGlobalScope(SoftDeletingScope::class)
			->with([
				'causer',
				'subject' => function (MorphTo $morphTo) {
					$morphTo->morphWith([
						Reward::class => ['event:id,name'],
						RewardClaim::class => ['reward:id,name', 'user:id,username,badge_id,badge_name'],
						TimeBonus::class => ['event:id,name'],
						TimeEntry::class => ['event:id,name', 'department:id,name', 'user:id,username,badge_id,badge_name'],
					]);
				},
			])
			->whereRaw('causer_id is distinct from subject_id')
			->orderByDesc('created_at');

		if ($this->days > 0) $query->where('created_at', '>', now()->subDays($this->days));
		return $query;
	}

	/** @var Activity $activity */
	public function map($activity, $excelDates = true): array {
		// Skip activities for time entries done by the owner themself
		if ($activity->subject_type === TimeEntry::class && $activity->subject?->user_id === $activity->causer_id) return [];

		$created = $activity->created_at->timezone(config('tracker.timezone'));

		return [
			$excelDates ? Date::dateTimeToExcel($created) : $created,
			$activity->causer?->audit_name,
			Str::replace('App\\Models\\', '', $activity->subject_type),
			$activity->subject->audit_name ?? $activity->subject->display_name ?? $activity->subject_id,
			static::buildParentList($activity),
			$activity->description,
			static::buildChangesList($activity),
		];
	}

	public function headings(): array {
		return [
			'Time',
			'Causer',
			'Type',
			'Name/ID',
			'Parent Entities',
			'Description',
			'Changes',
		];
	}

	public function columnFormats(): array {
		return [
			'A' => NumberFormat::FORMAT_DATE_DATETIME,
		];
	}

	public static function name(): string {
		return 'Audit Logs';
	}

	public static function slug(): string {
		return 'audit';
	}

	public static function defaultSortColumn(): int {
		return 0;
	}

	public static function defaultSortDirection(): string {
		return 'desc';
	}

	public static function extraParamKey(): string {
		return 'days';
	}

	public static function extraParamDefaultValue(): int {
		return 0;
	}

	public static function extraParamChoices(): array {
		return [
			1 => 'Last day',
			3 => 'Last 3 days',
			7 => 'Last week',
			30 => 'Last month',
			90 => 'Last 3 months',
			180 => 'Last 6 months',
			365 => 'Last year',
			0 => 'All',
		];
	}

	public static function afterSheet(AfterSheet $event) {
		static::formatAsTable($event->sheet);
	}

	/**
	 * Builds a semi-formatted list of parent names from an activity's subject. Example output:
	 *
	 * - User: Glitch is cute (#19)
	 * - Event: BLFC 2023
	 */
	private static function buildParentList(Activity $activity): ?string {
		$parents = [];
		if (isset($activity->subject->user)) $parents[] = "- User: {$activity->subject->user->audit_name}";
		if (isset($activity->subject->event)) $parents[] = "- Event: {$activity->subject->event->display_name}";
		if (isset($activity->subject->reward)) $parents[] = "- Reward: {$activity->subject->reward->display_name}";
		if (isset($activity->subject->department)) $parents[] = "- Department: {$activity->subject->department->display_name}";

		if (count($parents) === 0) return null;
		return implode("\n", $parents);
	}

	/**
	 * Builds a semi-formatted list of changes from an activity's change set. Example output:
	 *
	 * - stop: 2023-09-03T00:02:30.000000Z -> 2023-09-04T00:02:30.000000Z
	 * - start: 2023-09-02T22:02:30.000000Z -> 2023-09-01T22:02:30.000000Z
	 * - modifier: 1.25 -> 1.5
	 */
	private static function buildChangesList(Activity $activity): ?string {
		$changes = $activity->changes();

		// If there are old attribute values present, then we need to show both the old and new value
		if (isset($changes['old'])) {
			$changes['old'] = new Collection($changes['old']);
			return $changes['old']
				->map(function ($val, $key) use ($changes) {
					$old = $val ?? 'null';
					if (is_bool($old)) $old = $old ? 'true' : 'false';
					$new = $changes['attributes'][$key] ?? 'null';
					if (is_bool($new)) $new = $new ? 'true' : 'false';
					return "- {$key}: {$old} → {$new}";
				})
				->join("\n");
		}

		// Since there aren't any old attribute values present, we'll just list the current values plainly
		if (isset($changes['attributes'])) {
			$changes['attributes'] = new Collection($changes['attributes']);
			return $changes['attributes']
				->map(function ($val, $key) {
					$new = $val ?? 'null';
					if (is_bool($new)) $new = $new ? 'true' : 'false';
					return "- {$key}: {$new}";
				})
				->join("\n");
		}

		return null;
	}
}
