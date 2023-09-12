<?php

namespace App\Reports\Concerns;

interface WithExtraParam {
	/**
	 * Get the extra data key (used for the GET parameter)
	 */
	public static function extraParamKey(): string;

	/**
	 * Get the default value for the extra data
	 */
	public static function extraParamDefaultValue(): int;

	/**
	 * Get the premade choices for the extra data
	 */
	public static function extraParamChoices(): array;
}
