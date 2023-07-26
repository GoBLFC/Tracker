<?php

namespace App\Console\Commands;

use App\Models\QuickCode;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class PurgeStaleQuickCodes extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'quickcodes:purge';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Deletes stale quick codes from the database.';

	/**
	 * Execute the console command.
	 */
	public function handle() {
		$deleted = QuickCode::where('created_at', '<', now()->subSeconds(30))->delete();
		$pluralizedCode = Str::plural('code', $deleted);
		$this->info("Deleted {$deleted} stale quick {$pluralizedCode}.");
	}
}
