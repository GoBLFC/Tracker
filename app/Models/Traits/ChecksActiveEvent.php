<?php

namespace App\Models\Traits;

use App\Models\Setting;

trait ChecksActiveEvent {
	/**
	 * Checks whether the entity belongs to the active event
	 */
	public function isForActiveEvent(): bool {
		return $this->event_id === Setting::activeEvent()?->id;
	}
}
