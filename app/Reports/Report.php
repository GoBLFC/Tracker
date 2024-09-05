<?php

namespace App\Reports;

use App\Reports\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithProperties;

abstract class Report implements WithProperties {
	use Exportable, ToCollection;

	/**
	 * File types that reports can be exported as (file extension => pretty name)
	 *
	 * @property array<string, string>
	 */
	public const EXPORT_FILE_TYPES = [
		'xlsx' => 'Excel',
		'ods' => 'LibreOffice',
		'csv' => 'CSV',
		'pdf' => 'PDF',
		'html' => 'HTML',
	];

	/**
	 * Get the name of the report
	 */
	abstract public static function name(): string;

	/**
	 * Get a URL-friendly slug to represent the report
	 */
	abstract public static function slug(): string;

	/**
	 * Determine whether the report should be hidden from the list of possible reports
	 */
	public static function hide(): bool {
		return false;
	}

	/**
	 * Get the default column index to sort by
	 */
	public static function defaultSortColumn(): int {
		return 0;
	}

	/**
	 * Get the default direction to sort in
	 */
	public static function defaultSortDirection(): string {
		return 'asc';
	}

	/**
	 * Prefetch any data that may be needed for the report
	 */
	public function prefetch(): void {
		// nothing to do
	}

	/**
	 * Get a filename for the report
	 */
	public function filename(string $extension): string {
		$slug = static::slug();
		$date = now();
		return "{$slug}-{$date}.{$extension}";
	}

	public function properties(): array {
		return [
			'title' => static::name(),
		];
	}
}
