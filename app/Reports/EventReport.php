<?php

namespace App\Reports;

use App\Models\Event;
use Illuminate\Support\Str;

abstract class EventReport extends Report {
	public function __construct(public Event $event) {
		// nothing to do
	}

	public function filename(string $extension): string {
		$fileName = parent::filename($extension);
		$eventSlug = Str::slug($this->event->name);
		return "{$eventSlug}-{$fileName}";
	}

	public function properties(): array {
		$lcName = Str::lower(static::name());
		return array_merge([
			'description' => "{$this->event->name} {$lcName}",
		], parent::properties());
	}
}
