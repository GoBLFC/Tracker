<?php

namespace App\Reports\Concerns;

use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;

trait FormatsAsTable {
	/**
	 * Adds table formatting to the provided sheet
	 */
	public static function formatAsTable(Sheet $sheet) {
		$worksheet = $sheet->getDelegate();
		$highest = $worksheet->getCellCollection()->getHighestRowAndColumn();

		$table = new Table;
		$table->setName('DepartmentSummary');
		$table->setRange("A1:{$highest['column']}{$highest['row']}");
		$worksheet->addTable($table);
	}
}
