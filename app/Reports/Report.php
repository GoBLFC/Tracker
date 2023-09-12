<?php

namespace App\Reports;

use App\Reports\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithProperties;

abstract class Report implements WithProperties {
	use Exportable, ToCollection;

	/**
	 * Get the name of the report
	 */
	public static abstract function name(): string;

	/**
	 * Get a URL-friendly slug to represent the report
	 */
	public static abstract function slug(): string;

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
