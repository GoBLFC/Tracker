<?php

namespace App\Reports\Concerns;

interface WithTotals {
	/**
	 * Get the label for the totals row
	 */
	public function totalsLabel(): string;

	/**
	 * Get the list of functions for each column in the totals row
	 */
	public function totalsFunctions(): array;
}
