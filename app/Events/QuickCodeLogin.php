<?php

namespace App\Events;

use App\Models\QuickCode;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuickCodeLogin {
	use Dispatchable, SerializesModels;

	public function __construct(public readonly QuickCode $quickCode) {
		// nothing to do
	}
}
