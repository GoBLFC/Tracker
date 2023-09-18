<?php

namespace App\Reports\Concerns;

use RangeException;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

trait ToCollection {
	/**
	 * Collects the report data and combines it all into a collection
	 */
	public function toCollection(): Collection {
		// Get the collection from the report
		$collection = null;
		switch (true) {
			case $this instanceof FromQuery:
				$collection = $this->query()->get();
				break;
			case $this instanceof FromCollection:
				$collection = $this->collection();
				break;
			default:
				throw new RangeException('ToCollection trait is only usable on FromQuery or FromCollection instances.');
		}

		// Map and format the data if necessary
		$data = new Collection;
		if ($this instanceof WithMapping) {
			$collection = $collection->map(fn ($item) => $this->map($item, false));
			$collection = $collection->filter(fn ($item) => count($item) > 0);

			// if ($this instanceof WithColumnFormatting) {
			// 	// Build an array of formats, replacing the column letters with array indices
			// 	$colFormatting = $this->columnFormats();
			// 	$alphabet = range('A', 'Z');
			// 	$formats = [];
			// 	foreach ($colFormatting as $letter => $format) {
			// 		$formats[array_search($letter, $alphabet)] = $format;
			// 	}

			// 	// Format the value of each row
			// 	$collection = $collection->map(function ($cols) use ($formats) {
			// 		foreach ($cols as $key => $val) {
			// 			if (!isset($formats[$key])) continue;
			// 			switch ($formats[$key]) {
			// 				case NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1:
			// 					$cols[$key] = number_format($val, 2);
			// 					break;
			// 				case NumberFormat::FORMAT_NUMBER:
			// 					$cols[$key] = number_format($val, 0);
			// 					break;
			// 			}
			// 		}

			// 		return $cols;
			// 	});
			// }
		}

		// Assemble all data
		$data['body'] = $collection;
		if ($this instanceof WithHeadings) $data['head'] = new Collection($this->headings());
		if ($this instanceof WithTotals) {
			$data['totals'] = new Collection();
			$data['totals'][] = $this->totalsLabel();

			$functions = $this->totalsFunctions();
			foreach ($functions as $index => $function) {
				if ($index === 0) continue;
				switch ($function) {
					case 'sum':
						$data['totals'][] = $collection->sum($index);
						break;
					case 'none':
						$data['totals'][] = null;
						break;
					default:
						$data['totals'][] = $function;
				}
			}
		}

		return $data;
	}
}
