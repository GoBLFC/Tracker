<?php

namespace App\Reports\Concerns;

interface WithExtraData {
	/**
	 * Get the extra data key (used for the GET parameter)
	 */
	public static function extraDataKey(): string;

	/**
	 * Get the default value for the extra data
	 */
	public static function extraDataDefaultValue(): int;

	/**
	 * Get the premade choices for the extra data
	 */
	public static function extraDataChoices(): array;
}
